jQuery( document ).ready(function() {
	jQuery("#create-user").submit(function(event){
		event.preventDefault();
		var self = jQuery(this);

		jQuery.ajax({
      		url: ajax.ajaxurl, 
      		type: "POST",
      		data: self.serialize()+"&action=create_user",

      		beforeSend:function(){
      			jQuery("#create-user .error").remove()
      		},
      		success: function(response){
      			
       			if(response.status == 0){
       				self.prepend(`<div class="error"></div>`)
       				jQuery.each( response.error, function( i, val ) {
       					var error = `<p>${val}</p>`
       					jQuery("#create-user .error").append(error);
       				});

       			}
       			else if(response.status == 1){
       				self.prepend(`<div class="update"><p>${response.message}</p></div>`)
       				jQuery("#create-user")[0].reset()
       				jQuery(".confirguration-test").show()
       				jQuery("#confirguration-test input[name='user_login']").val(response.data.user_login)
       				jQuery("#confirguration-test input[name='password']").val(response.data.password)
       			}
      		}

    	});
	})
	jQuery("#confirguration-test").submit(function(event){
		event.preventDefault();
		var self = jQuery(this);
		jQuery.ajax({
      		url: ajax.ajaxurl, 
      		type: "POST",
      		data: self.serialize()+"&action=test_confirguration",

      		beforeSend:function(){
      			jQuery("#confirguration-test .error").remove()
      		},
      		success: function(response){
      			
       			if(response.status == 0){
       				self.prepend(`<div class="error"></div>`)
       				jQuery.each( response.error, function( i, val ) {
       					var error = `<p>${val}</p>`
       					jQuery("#confirguration-test .error").append(error);
       				});

       			}
       			else if(response.status == 1){
       				self.prepend(`<div class="update"><p>${response.message}</p></div>`)
       			}
      		}

    	});
	});
});