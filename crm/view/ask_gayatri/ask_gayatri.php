<div class="chat-container">
    <div class="chat-header">
        <h1><i class="fa fa-bolt"></i>&nbsp;&nbsp;Ask Gayatri</h1>
    </div>




    <div class="chat-messages" id="chat-messages">
    </div>
    <form method=" POST" id="frm_data_save">
        <div class="chat-input">
            <input type="text" id="user-input" name="user_input" required list="options" onkeypress="getQuestions(this.value)" placeholder="Type your message..." autocomplete="off">
            <datalist id="options">

            </datalist>
            <button id="send-button" type="submit"><i class="fa fa-send"></i></button>
    </form>
</div>

<script>
    getMessage();

    function getQuestions(value) {

        var base_url = $('#base_url_support').val();
        $.post(base_url + 'view/ask_gayatri/get_questions.php', {
            query: value
        }, function(data) {
            $('#options').html(data);
        });
    }

    function getMessage() {
        var client_id = $('#client_id').val();
        var base_url = $('#base_url_support').val();
        $.get(base_url + 'view/ask_gayatri/get_message.php', {
            client_id: client_id
        }, function(data) {
            $('#chat-messages').html(data);
            scrollToBottom();
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
                var base_url = $('#base_url_support').val();;
                var value = $('#user-input').val();
                if (value == '') {
                    error_msg_alert("Select From Suggestion Box");
                    return false;
                }
                const dataListOptions = $('#options').find('option').map(function() {
                    return $(this).val();
                }).get();

                let isValid = false;

                // Check if the user's input matches any option in the datalist
                if ($.inArray(value, dataListOptions) !== -1) {
                    isValid = true;
                }
                if (isValid == false) {
                    error_msg_alert('Select From Suggestion Box');
                    return false;
                    // Add your form submission logic here
                }
                var client_id = $('#client_id').val();

                $.post(base_url + 'view/ask_gayatri/store_message.php', {
                    query: value,
                    client_id: client_id
                }, function(data) {
                    $('#user-input').val('');
                    getMessage();
                });


            }
        })
    });
</script>