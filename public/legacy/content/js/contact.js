$(document).ready(function(){
    
    (function($) {
        "use strict";

    
    jQuery.validator.addMethod('answercheck', function (value, element) {
        return this.optional(element) || /^\bcat\b$/.test(value)
    }, "type the correct answer -_-");

    // validate contactForm form
    $(function() {
        $('#contactForm').validate({
            rules: {
                name: {
                    required: true,
                    minlength: 2
                },
                subject: {
                    required: true,
                    minlength: 4
                },
                number: {
                    required: true,
                    minlength: 10
                },
                email: {
                    required: true,
                    email: true
                },
                message: {
                    required: true,
                    minlength: 15
                }
            },
            messages: {
                name: {
                    required: "Vui lòng nhập họ tên",
                    minlength: "Họ tên có ít nhất 2 ký tự"
                },
                subject: {
                    required: "Vui lòng nhập tiêu đề",
                    minlength: "Tiêu đề có ít nhất 4 ký tự"
                },
                number: {
                    required: "Vui lòng nhập số điện thoại",
                    minlength: "Số điện thoại chứa ít nhất 10 ký tự"
                },
                email: {
                    required: "Vui lòng nhập địa chỉ email"
                },
                message: {
                    required: "Bạn chưa nhập nội dung góp ý",
                    minlength: "Nội dung góp ý có ít nhất 15 ký tự"
                }
            },
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    type:"POST",
                    data: $(form).serialize(),
                    url:"contact_process.php",
                    success: function() {
                        $('#contactForm :input').attr('disabled', 'disabled');
                        $('#contactForm').fadeTo( "slow", 1, function() {
                            $(this).find(':input').attr('disabled', 'disabled');
                            $(this).find('label').css('cursor','default');
                            $('#success').fadeIn()
                            $('.modal').modal('hide');
		                	$('#success').modal('show');
                        })
                    },
                    error: function() {
                        $('#contactForm').fadeTo( "slow", 1, function() {
                            $('#error').fadeIn()
                            $('.modal').modal('hide');
		                	$('#error').modal('show');
                        })
                    }
                })
            }
        })
    })
        
 })(jQuery)
})