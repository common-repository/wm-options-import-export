jQuery(document).ready(function($) {
    jQuery('#chkselecctall_top').click(function(event) {  //on click 
        if(this.checked) { // check select status
            jQuery('.checkbox1').each(function() { //loop through each checkbox
                this.checked = true;  //select all checkboxes with class "checkbox1"               
            });
        }else{
            jQuery('.checkbox1').each(function() { //loop through each checkbox
                this.checked = false; //deselect all checkboxes with class "checkbox1"                       
            });         
        }
    });
	
	jQuery("#form_export_optons").submit(function(){
		
		var checked_length =  jQuery('.checkbox1:checked').length;
		
		if(checked_length <= 0){
			alert("Please select at least one option.");
			return false;
		}
		
		return true;
	});
	
});