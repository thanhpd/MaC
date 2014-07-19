/*!
 * jQuery.Directory. The jQuery directory plugin
 *
 * Copyright (c) 2014 Tomas Zhu
 * http://tomas.zhu.bz
 * Support: http://tomas.zhu.bz/jquery-directory-plugin.html
 * Licensed under GPL licenses
 * http://www.gnu.org/licenses/gpl.html
 *
 * Launch  : June 2014
 * Version : 1.0.0
 * Released: 10 June, 2014 - 00:00
 * 
 */
(function($)
{
	$.fn.directory = function(options)
	{
		var defaults = {
				navigation: ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'],
				frontground: 'red',    
    			background: 'yellow'    // moved color to directory.css 
  			};
		var opts = $.extend(defaults, options);
		return this.each(function () {
			
			$currentthis = $(this);
			$($currentthis).find('span').each(function()
			{
				var currentstring = $.trim($(this).text()).toLowerCase();
				var firstAlpha = currentstring.charAt(0);
				$(this).data('alpha',firstAlpha);
			});
			var navbar = '<a class="navitem allDirectory" href="#">ALL</a>';
			$.each(defaults.navigation, function(i,val){
			navbar = navbar + '<a class="navitem '+val+'" href="#">'+val.toUpperCase()+'</a>';
  			});
  			navbar = '<div class="navitems">' + navbar + '</div>';
			$currentthis.prepend(navbar);
			
			$('.navitem').click(function()
			{
				$currentcheck = $(this);
				$clickedAlpha = $.trim($(this).text()).toLowerCase();
				$($currentthis).find('span').each(function()
				{
					var alpha = $(this).data('alpha');
					if ($clickedAlpha == alpha)
					{
						$(this).css('display','inline-block');
					}
					else
					{
						$(this).css('display','none');
					}
					
					if ($clickedAlpha == 'all')
					{
						$(this).css('display','inline-block');
					}
				});
  			});
		});
   };
})(jQuery);