jQuery(document).ready(function($) {
    // Initialize Select2 with Bootstrap 5 theme
    $('.cpc-select2').select2({
        placeholder: 'Select options',
        width: '100%'
    });

    // Education repeater
    let educationCount = 1;
    let websiteCount = 1;
    let emailCount = 1;
    let contactCount = 1;

    $('.add-education').click(function() {
        const template = $('.education-entry:first').clone();
        template.find('input').val('');
        template.find('input[name]').each(function() {
            const name = $(this).attr('name').replace('[0]', `[${educationCount}]`);
            $(this).attr('name', name);
        });
        template.find('select').val('');
        template.find('select[name]').each(function() {
            const name = $(this).attr('name').replace('[0]', `[${educationCount}]`);
            $(this).attr('name', name);
        });
        $('.cpc-education-repeater').append(template);
        educationCount++;
    });

    $(document).on('click', '.remove-education', function() {
        if ($('.education-entry').length > 1) {
            $(this).closest('.education-entry').remove();
        }
    });

    // Add Website
	$('.add-website').click(function() {
		const template = $('.website-entry:first').clone();
		template.find('input').val('');
		template.find('input').attr('name', `dip_website[${websiteCount}]`);
		$('.cpc-website-repeater').append(template);
		websiteCount++;
	});

	// Remove Website
	$(document).on('click', '.remove-website', function() {
		if ($('.website-entry').length > 1) {
			$(this).closest('.website-entry').remove();
		}
	});

    // Add Email
	$('.add-email').click(function() {
		const template = $('.email-entry:first').clone();
		template.find('input').val('');
		template.find('input').attr('name', `dip_email[${emailCount}]`);
		$('.cpc-email-repeater').append(template);
		emailCount++;
	});

	// Remove Email
	$(document).on('click', '.remove-email', function() {
		if ($('.email-entry').length > 1) {
			$(this).closest('.email-entry').remove();
		}
	});

    // Add Contact
	$('.add-contact').click(function() {
		const template = $('.contact-entry:first').clone();
		template.find('input').val('');
		template.find('input[name]').each(function() {
			const name = $(this).attr('name').replace(/\[\d+\]/, `[${contactCount}]`);
			$(this).attr('name', name);
		});
		$('.cpc-contact-repeater').append(template);
		contactCount++;
	});

	// Remove Contact
	$(document).on('click', '.remove-contact', function() {
		if ($('.contact-entry').length > 1) {
			$(this).closest('.contact-entry').remove();
		}
	});

    // Service checkbox logic
    $('.service-checkbox').on('change', function() {
        const $this = $(this);
        const parentId = $this.data('parent');
        const serviceId = $this.val();

        if ($this.is(':checked')) {
            // If it's a child and checked, ensure the parent is checked.
            if (parentId != 0) {
                $(`#service-${parentId}`).prop('checked', true);
            }
        } else {
            if (parentId == 0) {
                // If it's a parent and unchecked, uncheck all children.
                $(`.service-checkbox[data-parent="${serviceId}"]`).prop('checked', false);
            } else {
                // If it's a child and unchecked, check if any siblings are still checked.
                const $siblings = $(`.service-checkbox[data-parent="${parentId}"]`);
                const anyChecked = $siblings.is(':checked');

                if (!anyChecked) {
                    $(`#service-${parentId}`).prop('checked', false);
                }
            }
        }
    });


    // Sector checkbox logic.
    $('.sector-checkbox').on('change', function() {
        const $this = $(this);
        const parentId = $this.data('parent');
        const sectorId = $this.val();

        if ($this.is(':checked')) {
            // If a child is checked, ensure parent is checked.
            if (parentId != 0) {
                $(`#sector-${parentId}`).prop('checked', true);
            }
        } else {
            if (parentId == 0) {
                // If a parent is unchecked, uncheck all its children.
                $(`.sector-checkbox[data-parent="${sectorId}"]`).prop('checked', false);
            } else {
                // If a child is unchecked, check if all siblings are unchecked.
                const $siblings = $(`.sector-checkbox[data-parent="${parentId}"]`);
                const anyChecked = $siblings.is(':checked');

                if (!anyChecked) {
                    $(`#sector-${parentId}`).prop('checked', false);
                }
            }
        }
    });

    // Toggle icon for collapse.
    $('.collapse').on('show.bs.collapse', function () {
        var target = $('[data-bs-target="#' + this.id + '"] .toggle-icon');
        target.fadeOut(150, function() {
            $(this).text('-').fadeIn(150);
        });
    });
    
    $('.collapse').on('hide.bs.collapse', function () {
        var target = $('[data-bs-target="#' + this.id + '"] .toggle-icon');
        target.fadeOut(150, function() {
            $(this).text('+').fadeIn(150);
        });
    });
      
    // Fix Select2 with Bootstrap.
    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
    });

    // Hide the photo preview on page load
    $('#photo-preview').addClass('d-none');

    // Convert vanilla JS to jQuery for photo preview
    $('#dpc_photo').on('change', function (event) {
        var preview = $('#photo-preview');
        var file = event.target.files[0];

        if (file && file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function (e) {
                preview.attr('src', e.target.result);
                preview.removeClass('d-none');
            };
            reader.readAsDataURL(file);
        } else {
            // Hide preview if no valid image is selected
            preview.addClass('d-none');
            preview.attr('src', '');
        }
    });
});