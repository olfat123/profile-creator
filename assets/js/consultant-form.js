jQuery(document).ready(function($) {
    // Initialize Select2 with Bootstrap 5 theme
    $('.cpc-select2').select2({
        placeholder: 'Select options',
        width: '100%'
    });

    // Education repeater
    let educationCount = 1;
    
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

    // Service checkbox logic
    $('.service-checkbox').on('change', function() {
        const $this = $(this);
        const parentId = $this.data('parent');

        if ($this.is(':checked') && parentId != 0) {
            $(`#service-${parentId}`).prop('checked', true);
        }

        if (!$this.is(':checked') && parentId == 0) {
            $this.closest('.form-check').find('.service-checkbox').prop('checked', false);
        }
    });

    // Sector checkbox logic
    $('.sector-checkbox').on('change', function() {
        const $this = $(this);
        const parentId = $this.data('parent');
        
        if ($this.is(':checked') && parentId != 0) {
            $(`#sector-${parentId}`).prop('checked', true);
        }
        
        if (!$this.is(':checked') && parentId == 0) {
            $this.closest('.form-check').find('.sector-checkbox').prop('checked', false);
        }
    });

    // Toggle icon for collapse
    $('.cpc-services .collapse').on('show.bs.collapse', function() {
        $(this).prev().find('.toggle-icon').text('-');
    }).on('hide.bs.collapse', function() {
        $(this).prev().find('.toggle-icon').text('+');
    });

    // Fix Select2 with Bootstrap
    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
    });
});