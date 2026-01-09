require([
    'jquery',
    'mage/loader'
], function ($) {
 
    $( document ).ready(function() {
        //$('button[type="submit"]').on('click', function(){
        //show loader with timeout
        /*$('.orders_btn, .schagrs_btn, .quotes_btn, .invoices_btn, .credit_btn, .debit_btn, a .span-btn, .price-button, #cart-checkout-id, .backbtn,.back').on('click', function(){
             ShowLoaderWithTime();
        });*/
 
        //show loader without timeout , loader will gone after page load
        $('.orders-submenu, .scheduling-agreements-submenu, .quotes-submenu, .invoices-submenu, .creditMemos-submenu, .debitMemos-submenu, .showcart, .related-products .product-item-link, #order-reset-btn, .order-detail-container button, .total-invoice-td button, .backbtn .related-products .product-item-link, .related-products .product-item-photo, .compare-checkbox-with-content .custom-checkbox input, .clickable-row, a .span-btn, .price-button, #cart-checkout-id, .backbtn,.back,.advance-filter-box-button-box button, #search_mini_form .ab-btn, #compare-clear-all, #product-addtocart-button, #import-add-to-cart').on('click', function(){
            ShowLoaderWithoutTime();
        });
 
        //customer login form loader
        /* $('#send2').on('click', function(){
            if(($('#email').val() != "") && ($('#pass').val() != "")){
                ShowLoaderWithoutTime();
                //ShowLoaderLogin();
            }
        });*/
 
        //Enter Purchase Order continue
        /*$('#shipping-address-submit').on('click', function(){
            if(($('#name').val() != "")){
                ShowLoaderWithoutTime();
                //ShowLoaderLogin();
            }
        });*/
 
         $('#fileselect').on('change', function() {
            ShowLoaderWithoutTime();
           });
 
        //place order
       /* $('#process_order').on('click', function(){
               ShowLoaderWithoutTime();
                //ShowLoaderLogin();
        });*/
 
 
        function ShowLoaderWithoutTime()
        {
 
           //remove div from body if already exist
          $( ".loading-mask" ).remove();
 
           //add div to body
  $(document.body).append('<div class="loading-mask" data-role="loader" ><div class="all-loader"><p></p></div></div>'
          );
 
          //show loader and hide after some time
          $('.loading-mask').show();
          //setTimeout(function() { $(".loading-mask").fadeOut(500); }, 1000);
        }
 
        //for login submit - diffrent fn beacuse it need mre time for loader to show
        function ShowLoaderLogin()
        {
            //remove div from body if already exist
          $( ".loading-mask" ).remove();
 
           //add div to body
  $(document.body).append('<div class="loading-mask" data-role="loader" ><div class="all-loader"><p></p></div></div>'
          );
 
          //show loader and hide after some time
          $('.loading-mask').show();
         // setTimeout(function() { $(".loading-mask").fadeOut(500); }, 4000);
 
        }
 
        //The selectors in registration page to change if any selector changes the country code
        const selector = 'select[name$="_code"]'; // Matches all *_code selects
 
        // code to mask phone numbers based on the country code selected in drop down
        $(document).on('change', selector, function () {
            const countryCode = $(this).val();
            console.log("countryCode loader page = "+countryCode);
            $(selector).val(countryCode);
            if (countryCode === '+44') {
                console.log("iff loader page = "+countryCode);
 
                $('#country').val('GB');
                $('#country').prop('disabled', true);
                $('#region_id').prop('disabled', true);
                //----------- START Phone no masking For Credit Check ---------------
            }
            else {
                console.log("else loader page = "+countryCode);
                $('#country').val('');
                $('#country').prop('disabled', false);
                $('#region_id').prop('disabled', false);
                //----------- START Phone no masking For Credit Check ---------------
                $('.CC-InputCountryCode').mask('(000) 000-0000');
                //----------- END Phone no masking ---------------
                //----------- START Phone no masking for contact us/ Request info---------------
                $('.country-Code').mask('(000) 000-0000');
                 //----------- END Phone no masking ---------------
                //----------- START Phone no masking for Register/ edit Profiel---------------
                $('.R-InputCountryCode').mask('(000) 000-0000');
                //----------- END Phone no masking ---------------
            }
        });
    });
});