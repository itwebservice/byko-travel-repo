( function( $ ) {
	$( document ).ready(function() {
			
		$('form').attr('autocomplete', 'off');
		$('input').attr('autocomplete', 'off');
			
		$('#cssmenu li.has-sub>a').on('click', function(){
			$(this).removeAttr('href');
			var element = $(this).parent('li');
			if (element.hasClass('open')) {
				element.removeClass('open');
				element.find('li').removeClass('open');
				element.find('ul').slideUp();
			}
			else {
				element.addClass('open');
				element.children('ul').slideDown();
				element.siblings('li').children('ul').slideUp();
				element.siblings('li').removeClass('open');
				element.siblings('li').find('li').removeClass('open');
				element.siblings('li').find('ul').slideUp();
			}
		});

		$('#cssmenu>ul>li.has-sub>a').append('<span class="holder"></span>');

		function rgbToHsl(r, g, b) {
			r /= 255, g /= 255, b /= 255;
			var max = Math.max(r, g, b), min = Math.min(r, g, b);
			var h, s, l = (max + min) / 2;

			if(max == min){
				h = s = 0;
			}
			else {
				var d = max - min;
				s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
				switch(max){
					case r: h = (g - b) / d + (g < b ? 6 : 0); break;
					case g: h = (b - r) / d + 2; break;
					case b: h = (r - g) / d + 4; break;
				}
				h /= 6;
			}
			return l;
		}	
		
		// Counter Animation Start
		function inVisible(element) {
			//Checking if the element is
			//visible in the viewport
			var WindowTop = $(window).scrollTop();
			var WindowBottom = WindowTop + $(window).height();
			var ElementTop = element.offset().top;
			var ElementBottom = ElementTop + element.height();
			//animating the element if it is
			//visible in the viewport
			if ((ElementBottom <= WindowBottom) && ElementTop >= WindowTop)
			animate(element);
		  }
		  
		  function animate(element) {
			//Animating the element if not animated before
			if (!element.hasClass('ms-animated')) {
			  var maxval = element.data('max');
			  var html = element.html();
			  element.addClass("ms-animated");
			  $({
				countNum: element.html()
			  }).animate({
				countNum: maxval
			  }, {
				//duration 5 seconds
				duration: 5000,
				easing: 'linear',
				step: function() {
				  element.html(Math.floor(this.countNum) + html);
				},
				complete: function() {
					element.html(this.countNum + html);
				}
			  });
			}
			
		}
		  
		  //When the document is ready
		  $(function() {
			$(document).ready(function() {
			  $(".dashboard-counter").each(function() {
				inVisible($(this));
			  });
			})
		  });		  
		  // Counter Animation End
		  
		  
		  // Table Drag Drop Start
		//   $("#sortable tbody").sortable({
		// 	cursor: "move",
		// 	placeholder: "sortable-placeholder",
		// 	helper: function(e, tr)
		// 	{
		// 	  var $originals = tr.children();
		// 	  var $helper = tr.clone();
		// 	  $helper.children().each(function(index)
		// 	  {
		// 	  // Set helper cell sizes to match the original sizes
		// 	  $(this).width($originals.eq(index).width());
		// 	  });
		// 	  return $helper;
		// 	}
		//   }).disableSelection();
		  $( "#sortable tbody" ).sortable( {
			update: function( event, ui ) {
			$(this).children().each(function(index) {
					$(this).find('td').last().html(index + 1)
			});
		  }
		});
		  // Table Drag Drop End
		
	});
} )( jQuery );
