
// To autcomplete the input in the form
function autoComplete(element, value, type){
    element.autocomplete({
        source: function (request, response) {
            $.getJSON("retrieve_data_autocomplete.php", {
                term: value,
                type: type
            }, response);
        }
    });
}

//
function makeFileList() {
    //get the input and UL list
    var input = document.getElementById('documentInput');
    var list = document.getElementById('fileList');

    //empty list for now...
    while (list.hasChildNodes()) {
        list.removeChild(list.firstChild);
    }

    if ( input.files.length > 0 ) {
        var ul = document.createElement('ul');
        ul.setAttribute('class', 'in_out');

        //for every file...
        for (var x = 0; x < input.files.length; x++) {
            //add to list
            var li = document.createElement('li');
            li.innerHTML = input.files[x].name;
            ul.appendChild(li);
        }

        list.appendChild(ul);
    }
}

//
function open_page(url) {
	window.open(url, '_top');
	return false;
}

//
function open_page_blank(url) {
	window.open(url, '_blank');
	return false;
}

//
function doc_submit(pressedbutton) {
	document.getElementById("pressedbutton").value = pressedbutton;
	document.getElementById("frmA").submit();

	return true;
}
