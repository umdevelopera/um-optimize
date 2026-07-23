/*
 Plugin Name: Ultimate Member - Optimize and Color
 Description: Profile tab AJAX loading
 Version:     1.4.0
 Author:      umdevelopera
 Author URI:  https://github.com/umdevelopera
 */

jQuery(function () {
	const $profile = jQuery('div.um.um-profile');
	const exeptions = [
		'comments',
		'forumwp',
		'jobboardwp',
		'jobboardwp_dashboard'
	];

	const tabs = {};
	save_current_tab();

	// Navbar click.
	$profile.on('click', '.um-profile-navbar a', event_load_tab);

	// Profile menu click.
	$profile.on('click', '.um-profile-nav a', event_load_tab);

	// Profile submenu click.
	$profile.on('click', '.um-profile-subnav a', event_load_tab);

	function event_load_tab(e) {
		const $a = jQuery(e.currentTarget);
		const url = new URL($a.prop('href'));
		const nav = url.searchParams.get('profiletab');
		const subnav = url.searchParams.get('subnav') || 'default';

		// Show existing tab.
		if (tabs[ nav ] && tabs[ nav ][ subnav ]) {
			switch_tab($a, nav, subnav);
			return false;
		}

		// Tab that can not be loaded by AJAX.
		if (exeptions.includes(nav)) {
			return true;
		}

		// Load new tab.
		const form_id = $profile.find('div[data-form_id]').data('form_id');
		const user_id = $profile.find('div[data-user_id]').data('user_id');
		if (form_id && user_id && url.searchParams.has('profiletab')) {

			jQuery.ajax({
				url: wp.ajax.settings.url,
				method: 'get',
				dataType: 'html',
				data: {
					action: 'um_optimize_profile_tab',
					nonce: um_scripts.nonce,
					nav: nav,
					subnav: subnav,
					form_id: form_id,
					user_id: user_id
				},
				success: function (data) {
					const $data = jQuery(data);

					if (!tabs[ nav ])
						tabs[ nav ] = {};
					tabs[ nav ]['subnav'] = $data.filter('div.um-profile-subnav');
					tabs[ nav ][ subnav ] = $data.filter('div.um-profile-body');

					if ('default' === subnav && tabs[ nav ]['subnav'].length) {
						const $subnavA = tabs[ nav ]['subnav'].find('a.active');
						if ($subnavA.length) {
							const subnavURL = new URL($subnavA.prop('href'));
							const subnavSubnav = subnavURL.searchParams.get('subnav');
							if (subnavSubnav) {
								tabs[ nav ][ subnavSubnav ] = tabs[ nav ][ subnav ];
							}
						}
					}

					switch_tab($a, nav, subnav);
					$a.removeAttr('style').find('i').removeClass('fa-spin');
				},
				beforeSend: function () {
					$a.css('cursor', 'progress').find('i').addClass('fa-spin');
				}
			});

			return false;
		}
	}

	function init_libs( nav, subnav ) {

		// for Groups.
		if ( 'function' === typeof jQuery.fn.select2 ) {
			const $tabContent = $profile.find('div.um-profile-body');

			$tabContent.find('.um-s1, .um-s2, .um-s3').filter('.select2-hidden-accessible')
							.select2('destroy').off('select2:select')
							.siblings('span.select2').remove();

			$tabContent.find('.um-s1').each(function () {
				var obj = jQuery(this);
				obj.select2({
					allowClear: true,
					dropdownParent: obj.parent()
				});
			});

			$tabContent.find('.um-s2').each(function () {
				var obj = jQuery(this);
				var atts = {
					allowClear: false
				};
				if (!obj.parents('.um-custom-shortcode-tab').length) {
					atts = {
						allowClear: false,
						minimumResultsForSearch: 10,
						dropdownParent: obj.parent()
					};
				}
				obj.select2(atts);
			});

			$tabContent.find('.um-s3').each(function () {
				var obj = jQuery(this);
				obj.select2({
					allowClear: false,
					minimumResultsForSearch: -1,
					dropdownParent: obj.parent()
				});
			});
		}

		// for Messages.
		if ( 'messages' === nav && 'function' === typeof um_messaging_init_conversations_list ) {
			um_messaging_init_conversations_list();
		}

		// for Notes.
		if ( 'function' === typeof jQuery.fn.masonry && jQuery('.um-notes-grid').length ) {
			jQuery('.um-notes-grid').masonry({
				itemSelector: '.note-block'
			});
		}

		// for Reviews.
		if ( 'reviews' === nav && 'function' === typeof init_rating_fields ) {
			init_rating_fields();
		}
	}

	function save_current_tab() {
		// Search active profile submenu item.
		let $a = $profile.find('.um-profile-subnav a.active');
		if (!$a.length) {
			// Search active profile menu item.
			$a = $profile.find('.um-profile-nav-item.active a');
		}
		if (!$a.length) {
			// Search active navbar item.
			$a = $profile.find('.um-profile-navbar a.current');
		}

		if ($a.length) {
			const url = new URL($a.prop('href'));
			const nav = url.searchParams.get('profiletab');
			const subnav = url.searchParams.get('subnav') || 'default';

			if (!tabs[ nav ])
				tabs[ nav ] = {};
			tabs[ nav ]['subnav'] = $profile.find('div.um-profile-subnav');
			tabs[ nav ][ subnav ] = $profile.find('div.um-profile-body');
		}
	}

	function switch_tab($a, nav, subnav) {
		// update URL.
		history.replaceState({}, '', $a.prop('href'));

		// update navbar.
		const $navbar = $a.closest('.um-profile-navbar');
		if ($navbar.length) {
			$profile.find('.um-profile-nav-item.active').removeClass('active');
			$navbar.find('a.current').removeClass('current');
			$a.addClass('current');
		}

		// update profile menu.
		const $navItem = $a.closest('.um-profile-nav-item');
		if ($navItem.length) {
			$profile.find('.um-profile-navbar a.current').removeClass('current');
			$navItem.siblings('.active').removeClass('active');
			$navItem.addClass('active');
		}

		// update profile submenu.
		const $subnav = $a.closest('.um-profile-subnav');
		if ($subnav.length) {
			$a.siblings('.active').removeClass('active');
			$a.addClass('active');
		} else {
			$profile.find('div.um-profile-subnav').remove();
			$profile.find('div.um-profile-nav').after(tabs[ nav ]['subnav']);
		}

		// update tab content.
		$profile.find('div.um-profile-body').replaceWith(tabs[ nav ][ subnav ]);

		// update styles.
		init_libs( nav, subnav );
		um_responsive();
		jQuery(window).trigger('load');
	}
});