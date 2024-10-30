
jQuery(document).ready(function () {
//     jQuery('.mrp_rl_products').select2();
//   //  jQuery('.mrp-product-no').select2();
//    // jQuery('.mrp-category-no').select2();
    
// 	// multiple reward points for order
// 	jQuery(document).on( 'click', '.mrp-order-specific-hook', function() {
//         var hook = jQuery(this).closest('.hook-instance').clone();
//         hook.find('input.mrp-order-no').val('10');
// 		hook.find('input.mrp-order-creds').val('2');
// 		hook.find('input.mrp-extra-creds').val('50');
//         jQuery(this).closest('.widget-content').append( hook );
// 	}); 
	
	
//     jQuery(document).on( 'click', '.mrp-order-remove-specific-hook', function() {
//         var container = jQuery(this).closest('.widget-content');
//         if ( container.find('.hook-instance').length > 1 ) {
//             var dialog = confirm("Are you sure you want to remove this hook?");
//             if (dialog == true) {
//                 jQuery(this).closest('.hook-instance').remove();
//             } 
//         }
//     }); 
// 	// multiple reward points for order ends


//     // multiple reward points for products
// 	jQuery(document).on( 'click', '.mrp-product-specific-hook', function() {
//         var currentdate = new Date();
//         var cDate = currentdate.toISOString().substring(0,10);
//         var hook = jQuery(this).closest('.hook-instance').clone();
//         hook.find('input.mrp-product-no').val('0');
// 		hook.find('input.mrp-product-creds').val('2');
// 		hook.find('input.mrp-product-start').val(cDate);
//         hook.find('input.mrp-product-end').val(cDate);
//         jQuery(this).closest('.widget-content').append( hook );
// 	}); 
	
	
//     jQuery(document).on( 'click', '.mrp-product-remove-specific-hook', function() {
//         var container = jQuery(this).closest('.widget-content');
//         if ( container.find('.hook-instance').length > 1 ) {
//             var dialog = confirm("Are you sure you want to remove this hook?");
//             if (dialog == true) {
//                 jQuery(this).closest('.hook-instance').remove();
//             } 
//         }
//     }); 
//     jQuery(document).on( 'change', '.mrp-product-no', function(e) {
//         var selected = jQuery(e.target).val();
//         jQuery(this).next(".mrp-product-no-hidden").val(selected.join(","));
//     });
// 	// multiple reward points for products ends

//      // multiple reward points for category
// 	jQuery(document).on( 'click', '.mrp-category-specific-hook', function() {
//         var currentdate = new Date();
//         var cDate = currentdate.toISOString().substring(0,10);
//         var hook = jQuery(this).closest('.hook-instance').clone();
//         hook.find('input.mrp-category-no').val('0');
// 		hook.find('input.mrp-category-creds').val('2');
// 		hook.find('input.mrp-category-start').val(cDate);
//         hook.find('input.mrp-category-end').val(cDate);
//         jQuery(this).closest('.widget-content').append( hook );
// 	}); 
	
	
//     jQuery(document).on( 'click', '.mrp-category-remove-specific-hook', function() {
//         var container = jQuery(this).closest('.widget-content');
//         if ( container.find('.hook-instance').length > 1 ) {
//             var dialog = confirm("Are you sure you want to remove this hook?");
//             if (dialog == true) {
//                 jQuery(this).closest('.hook-instance').remove();
//             } 
//         }
//     }); 

//     jQuery(document).on( 'change', '.mrp-category-no', function(e) {
//         var selected = jQuery(e.target).val();
//         jQuery(this).next(".mrp-category-no-hidden").val(selected.join(","));
        
//     });
// 	// multiple reward points for category ends
	

});