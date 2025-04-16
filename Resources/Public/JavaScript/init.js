document.addEventListener("DOMContentLoaded", function () {


  /**
   * Takes a form node and sends it over AJAX.
   * @param {HTMLFormElement} form - Form node to send
   * @param {function} callback - Function to handle onload.
   *                              this variable will be bound correctly.
   */

  function ajaxPost(form, callback) {
    const url = form.action,
      xhr = new XMLHttpRequest();

    //This is a bit tricky, [].fn.call(form.elements, ...) allows us to call .fn
    //on the form's elements, even though it's not an array. Effectively
    //Filtering all the fields on the form
    var params = [].filter.call(form.elements, function (el) {
      //Allow only elements that don't have the 'checked' property
      //Or those who have it, and it's checked for them.
      return typeof (el.checked) === 'undefined' || el.checked;
      //Practically, filter out checkboxes/radios which aren't checked.
    })
      .filter(function (el) {
        return !!el.name;
      }) //Nameless elements die.
      .filter(function (el) {
        return !el.disabled;
      }) //Disabled elements die.
      .map(function (el) {
        //Map each field into a name=value string, make sure to properly escape!
        return encodeURIComponent(el.name) + '=' + encodeURIComponent(el.value);
      }).join('&'); //Then join all the strings by &

    xhr.open("POST", url);
    xhr.setRequestHeader("Content-type", "application/x-form-urlencoded");

    //.bind ensures that this inside of the function is the XHR object.
    xhr.onload = callback.bind(xhr);

    //All preparations are clear, send the request!
    xhr.send(params);
  }

  const formhandlerInit = function (form) {
    const actionButtons = document.querySelectorAll('button[data-formhandler-action]');

    if (actionButtons) {
      actionButtons.forEach((button) => {
        button.addEventListener('click', (e) => {
          e.preventDefault();

          const action = button.dataset.formhandlerAction;
          if (action === 'remove') {
            fetch('/?eID=formhandler-remove-file', {
              method: "POST",
              body: JSON.stringify({
                token: button.closest('form').querySelector('input[name$="[randomId]"]').value,
                field: button.dataset.field,
                i: button.dataset.index
              })
            })
              .then(function (response) {
                if (response.status === 200) {

                  const removeAfterSelector = button.dataset.removeAfter;
                  if (removeAfterSelector) {
                    const toRemove = button.closest(button.dataset.removeAfter);
                    if (toRemove) {
                      toRemove.remove();
                    }
                  } else {
                    button.parentElement.remove();
                  }
                }

              })
          }
        })
      });

      if (TYPO3.settings.formhandler.ajaxSubmit) {
        const valuesPrefix = TYPO3.settings.formhandler.formValuesPrefix;

      }
    }

  }

  formhandlerInit();


});
