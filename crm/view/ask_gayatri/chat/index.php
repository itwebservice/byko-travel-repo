<style>
.image-paste-div
{
    width: 83%;
    display: flex;
    flex-direction: column;
    flex-wrap: nowrap;
    position: initial;
}

#options
{
    position: absolute;
    bottom: 60px;
    width: auto;
    border-radius: 6px;
    left: 12px;
    background-color: #ededed;
    width: 83%;
    max-height: 400px;
    overflow-x: auto;
    color: #4850eb;
}


#options li
{
    padding: 12px;
    cursor: pointer;
    border-bottom: 1px solid #efe8e8;
}
#options li:hover
{
   background: gainsboro;
}
#image-paste-view
{
    position: absolute;
    bottom:60px;
    width: auto;
    border-radius: 6px;
    left:12px;
    background-color: #F8F8F8;
    width: 83%;
}
#image-paste-view img
{
    max-width: 200px;
    max-height: 200px;
    padding: 5px;
    background: #c1c1c1;
}
.chat-input{
    position:relative;
}
@media only screen and (max-width: 600px) {
  #image-paste-view{
    width: 62%;  
  }
}
</style>
<div class="chat-container">
    <div class="chat-header">
        <h1><i class="fa fa-globe"></i>&nbsp;&nbsp;Helpdesk</h1>
    </div>

    <div class="chat-messages" id="chat-messages">
    </div>
    
	  <div class="reply-input" id="replyBox">
        <i class="fa fa-close clse-btn" onclick="toggleReplyBox()"></i>
        <div class="text" id="old-chat"></div>
    </div>
<form method=" POST" id="frm_data_save">
        
        <div class="chat-input">
            <input type="hidden" id="reply_id" value="0">
            <div id="image-paste-view"></div>
            <textarea style="position: relative;" name="user_input" id="user-input" rows="2"  placeholder="Type your message..." autocomplete="off"></textarea>
             <ul id="options" style="display:none;">

            </ul>
            <button id="send-button" type="submit" onclick="toggleReplyBox()"><i class="fa fa-send"></i></button>
    </form>

	
    <form method="POST" id="fileUploadForm" enctype="multipart/form-data">
        <!-- file -->
        &nbsp;&nbsp;&nbsp;
        <label for="file" class="custom-file-upload">
            <i class="fa fa-paperclip"></i> Attach File
        </label>
        <input type="file" name="file" id="file">
        <!-- file -->
    </form>
</div>
<script>
$(document).ready(function() {
$(document).on("keyup","#user-input",function() {
    
    var value=$(this).val();
    if(value=='')
    {
        $('#options').hide();
    }
    else
    {
     var base_url = $('#base_url_support').val();
    $.post(base_url + 'view/ask_gayatri/chat/get_questions.php', {
        query: value
    }, function(data) {
        if(data)
        {
        $('#options').html(data);
        $('#options').show();
        }
    });
   
    }
});

$(document).on("click","#options li",function() {
    var litext=$(this).text();
    if(litext!='')
    {
        $("#user-input").val(litext);
    }
    $('#options').hide();
});

// Click outside handler to hide options
        $(document).on("click", function(e) {
            if (!$(e.target).closest('#options').length && !$(e.target).closest('#user-input').length) {
                $('#options').hide();
            }
        });

 });
    

var input = document.querySelector("#user-input");
var fileInput = document.getElementById("file");
input.addEventListener("paste",function(event)
{
    var items = (event.clipboardData || event.originalEvent.clipboardData).items;
    for (index in items) {
        $("#image-paste-view").html("");
        var item = items[index];
        if (item.kind === 'file') {
            var blob = item.getAsFile();
            var reader = new FileReader();
            reader.onload = function(event){
                let img = document.createElement('img')
                img.src = event.target.result
                document.getElementById('image-paste-view').appendChild(img);
                var btnDelete = document.createElement('i');
                    btnDelete.style.position = 'absolute';
                    btnDelete.style.width = "20px";
                    btnDelete.style.height = "20px";
                    btnDelete.style.borderRadius = "50%";
                    btnDelete.style.top = "10px";
                    btnDelete.style.left = "170px";
                    btnDelete.style.left = "170px";
                    btnDelete.style.paddingTop = "3px";
                    btnDelete.style.paddingLeft = "4px";
                    btnDelete.style.background = "#f2f2f2";
                    btnDelete.id = 'itemDelete';
                    btnDelete.className  = 'fa fa-trash';
                    btnDelete.style.zIndex = "999999";
                document.getElementById('image-paste-view').appendChild(btnDelete);
                
                let input = document.createElement('input')
                input.name = "pasteImage"
                input.type = "hidden"
                input.id = "pasteImage"
                input.value = event.target.result
                document.getElementById('image-paste-view').appendChild(input);
                
               
                // setDataForImage(blob);
            };
            reader.readAsDataURL(blob);
        }
    }
});

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#image_upload_preview').attr('src', e.target.result);
            setDataForImage(e.target);
        }
        reader.readAsDataURL(input.files[0]);
    }
}


$(document).on("click","#itemDelete",function() {
    $('#image-paste-view').html("");
});
</script>
<script>
    getMessage();



    function getMessage() {
        var client_id = $('#client_id').val();
        var base_url = $('#base_url_support').val();
        $.get(base_url + 'view/ask_gayatri/chat/get_chat_message.php', {
            client_id: client_id,
            user_side: "Client"
        }, function(data) {
            $('#chat-messages').html(data);
            scrollToBottom();
        });
    }

    setInterval(() => {
        getUnreadMessage();
    }, 2000);


    function getUnreadMessage() {
        var client_id = $('#client_id').val();
        var base_url = $('#base_url_support').val();
        $.get(base_url + 'view/ask_gayatri/chat/get_chat_message_unread.php', {
            client_id: client_id,
            user_side: "Client"
        }, function(data) {
            $('#chat-messages').append(data);
            // scrollToBottom();
        });
    }


    function scrollToBottom() {
        var chatMessages = $('#chat-messages');
        chatMessages.scrollTop(chatMessages.prop('scrollHeight'));
    }


    $(function() {
        $('#frm_data_save').validate({
            rules: {
                rules: {
                    user_input: {
                        required: true
                    },
                }
            },
            submitHandler: function(form) {
                var base_url = $('#base_url_support').val();
                var value = convertToHtml($('#user-input').val());
                var user_side_id = 0;
                var client_name = $('#client_name').val();
                var client_id = $('#client_id').val();
                
                var baseimage='';
                var is_url=0;
                if($("#pasteImage").val())
                {
                    baseimage=$("#pasteImage").val();
                }
                if(baseimage == '')
                {
                    if (value == '') 
                    {    
                            error_msg_alert("Message Is Required");
                            return false;
                    }
                } 
                else
                {
                    baseimage=baseimage;
                    is_url= 1;
                }

                $.post(base_url + 'view/ask_gayatri/chat/store_chat_message.php', {
                    client_id: client_id,
                    user_side: 'Client',
                    user_side_id: user_side_id,
                    message: value,
                    reply_id: 0,
                    client_name: client_name,
                    baseimage:baseimage,
                    is_url:is_url,
                }, function(data) {
                    $('#user-input').val('');
                    // getMessage();
                    if(baseimage!='')
                    {
                      $("#image-paste-view").html("");   
                    }
                    getUnreadMessage();
                    setTimeout(() => {
                        scrollToBottom();
                    }, 1000);
                });


            }
        })
    });
</script>


<script>
    $(document).ready(function() {
        $("#file").change(function() {
            var formData = new FormData($("#fileUploadForm")[0]);
            var base_url = $('#base_url_support').val();
             var user_side_id = 0;
            var client_id = $('#client_id').val();
            var client_name = $('#client_name').val();

            var fileInput = $("#fileUploadForm input[type='file']")[0];
            var selectedFile = fileInput.files[0];
            if (!selectedFile) {
                return false;
            }
            if (fileInput == false && fileInput.files.length == 0) {
                return false;
            }
            if (selectedFile.size > 100 * 1024 * 1024) {
                error_msg_alert("File Size Limit Is 100 MB");
                return false;
            }
            $.ajax({
                url: base_url + "view/ask_gayatri/upload.php", // Change this to the path of your PHP file
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    var fileName = response.split("/");
                    if (fileName[(fileName.length - 1)] == "error") {
                        return false;
                    }
                    var message = `File:` + fileName[(fileName.length - 1)] + `<br>`;
                    $.post(base_url + 'view/ask_gayatri/chat/store_chat_message.php', {
                        client_id: client_id,
                        user_side: 'Client',
                        user_side_id: user_side_id,
                        message: message,
                        reply_id: 0,
                        is_url: 1,
                        url: response,
                        client_name: client_name
                    }, function(data) {
                        $('#user-input').val('');
                        getUnreadMessage();
                        setTimeout(() => {
                            scrollToBottom();
                        }, 1000);
                    });
                },
                error: function() {
                    error_msg_alert("Error uploading the file.");
                    return false;
                }
            });
        });
    });

    function convertToHtml(text) {


        // Replace line breaks with <br> tags
        var html = text.replace(/\n/g, '<br>');

        return html;
    }
	
	  function toggleReplyBox() {
        var replyBox = document.getElementById("replyBox");
        if (replyBox.classList.contains("show-box")) {
            replyBox.classList.remove("show-box");
            $('#reply_id').val(0);
            $('#fileUploadForm').show();
        } else {
            replyBox.classList.add("show-box");
            $('#fileUploadForm').hide();
        }
    }
</script>

<script>
    $(document).ready(function() {
        const textarea = $('#user-input');
        const form = $('#frm_data_save');

        textarea.on('keydown', function(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault(); // Prevent default form submission
                form.submit(); // Submit the form
            } else if (event.key === 'Enter' && event.shiftKey) {
                // Insert a line break
                const start = this.selectionStart;
                const end = this.selectionEnd;
                const text = this.value;
                this.value = text.substring(0, start) + '\n' + text.substring(end);
                this.selectionStart = this.selectionEnd = start + 1; // Place the cursor after the line break
                event.preventDefault(); // Prevent the default Enter key behavior
            }
        });
    });
	
	 function replyMessage(messageId) {
        var base_url = $('#base_url').val();
        var user_side_id = $('#user_side_id').val();
        var client_id = $('#client_id').val();
        toggleReplyBox();
        var oldChat = $('#messageId_' + messageId).html();
        $('#reply_id').val(messageId);
        $('#old-chat').html(oldChat);

        // $.post(base_url + 'view/ask_gayatri/chat/store_chat_message.php', {
        //     client_id: client_id,
        //     user_side: 'Support',
        //     user_side_id: user_side_id,
        //     reply_id: messageId,
        // }, function(data) {
        //     getUnreadMessage();
        //     setTimeout(() => {
        //         scrollToBottom();
        //     }, 1000);
        // });

    }
</script>