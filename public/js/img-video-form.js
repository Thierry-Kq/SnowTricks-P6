$(document).ready(function () {

    //  * jquery to add / remove a field for a videoTrick --------------------
    // var max_fields = 10; //maximum input boxes allowed
    var wrapper = $(".input_fields_wrap"), //Fields wrapper
        add_button = $(".add_field_button"), //Add button ID
        count = 1; //initlal text box count
    $(add_button).click(function (e) { //on add input button click
        e.preventDefault();
        // if (count < max_fields) { //max input box allowed
        count++; //text box increment
        $(wrapper).append('<div><input type="text" id="form_videos_' + count + '"' + 'name="tricks[videos][' + count + ']"/><a href="#" class="remove_field">Remove</a></div>'); //add input box
        // }
    });

    $(wrapper).on("click", ".remove_field", function (e) { //user click on remove text
        e.preventDefault();
        $(this).parent('div').remove();
        // count--;
    })

    // * jquery/ajax to delete image/videoTrick in db and remove field --------------------
    var $a = $("a[data-delete]");

    $a.click(function (e) {
        e.preventDefault();

        var $a = $(this);
        var token = $(this).attr("data-token");
        var url = $(this).attr("href");
        if (confirm("Sur ???")) {

            // fetch = requete (jusquau premier then)
            fetch(url, {
                method: "DELETE",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({"_token": token})
            }).then(
                //    recup la reponse json
                response => response.json()
            ).then(data => {
                // fais des choses avec la response
                if (data.success)
                    $a.parent().remove()
                else
                    alert(data.error)

            }).catch(e => alert(e))
            // alert(url);
        }
    })
})
