// Create the class

var myStoresmsCounter = Class.create({
    initialize: function(eventToObserve) // Is called when the page has finished loading by the Event.observe code below
    {

        var storesmsCounter = document.getElementById('storesmsCounter');
        var storesmsCounterContainer = document.getElementById('storesmsCounterContainer');
        var maxchars = 160;
        var childAdded = false;
        var activeTextArea = false;
        var textAreaId = false;
//        var storeNameLenght = document.getElementById('storesms_main_conf_storename').value.length;

        $('storesms_templates').observe(eventToObserve, function(event) {
            var textlength = 0;
            activeTextArea = event.findElement('textarea');

            if (activeTextArea) {
                

                if (textAreaId !== activeTextArea.id) {
                    storesmsCounterContainer.remove(); //remove old storesmsCounterContainer
                    activeTextArea.insert({//reinitialize conterContainer in new position
                        after: storesmsCounterContainer
                    });
                    storesmsCounterContainer.show(); //snow storesmsCounter div at starts
                }

                textlength = activeTextArea.value.length;
                
                
                
                storesmsCounter.update(textlength);

                if (textlength <= (maxchars - 50)) {

                    $('storesmsCounter').setStyle({
                        fontSize: '150%',
                        fontWeight: 'normal',
                        color: '#0F910F'
                    });
                    $('storesmsTooLongAlert').hide();

                }
                else if (textlength <= (maxchars - 20)) {

                    $('storesmsCounter').setStyle({
                        fontWeight: 'bold',
                        color: '#FF8400'
                    });
                    $('storesmsTooLongAlert').hide();

                } else {

                    $('storesmsCounter').setStyle({
                        fontWeight: ' bold',
                        color: '#B80000'
                    });
                    $('storesmsTooLongAlert').show();

                }


            }




        });

    }

});
// Global variable for the instance of the class
// Creating an instance of the class if the page has finished loading
Event.observe(window, 'load', function() {
    document.getElementById('storesmsCounterContainer').hide(); //hide storesmsCounter div at start
    new myStoresmsCounter('click');
    new myStoresmsCounter('keyup');
});