$(document).ready(function(){
    $(".datepicker").datepicker({
        format: 'dd.mm.yyyy',
        autoclose:true,
        language: 'de'
    });
    tinymce.init({
        selector: '.textarea',
        height: 500,
        theme: 'modern',
        plugins: 'print preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists textcolor wordcount imagetools contextmenu colorpicker textpattern',
        toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat',
        image_advtab: true,
        templates: [
        { title: 'Test template 1', content: 'Test 1' },
        { title: 'Test template 2', content: 'Test 2' }
        ],
        content_css: [
        '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
        '//www.tinymce.com/css/codepen.min.css'
        ],
        force_br_newlines : false,
        force_p_newlines : false,
        forced_root_block : ''
    });

    $(".btn.btn-danger[type='submit']").click(function() {
        var confirm = $(this).data("confirm");
        if(confirm) {
            var answer = prompt('Das Löschen kann nicht rückgängig gemacht werden. Bitte bestätige die Aktion, in dem du den Namen eingibst:');

            if(answer !== confirm) return false;
        }
        else {
            if(window.confirm('Die Aktion kann nicht rückgängig gemacht werden. Willst du wirklich fortfahren?') === false) {
                return false;
            }
        }
    });
});