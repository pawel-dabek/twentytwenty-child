/**
 * Vanilla JS way of making AJAX call.
 */
document.addEventListener('DOMContentLoaded', function () {
  const data = new URLSearchParams();
  data.append('action', 'get_books');
  data.append('nonce', my_ajax_object.nonce);

  fetch(my_ajax_object.ajax_url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
    },
    body: data,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        console.log(data.data);
      } else {
        console.error('AJAX request failed');
      }
    })
    .catch((error) => {
      console.error('AJAX request error:', error);
    });
});

/**
 * jQuery way of making AJAX call.
 */
jQuery(document).ready(function ($) {
  $.ajax({
    url: my_ajax_object.ajax_url,
    type: 'POST',
    data: {
      action: 'get_books',
      nonce: my_ajax_object.nonce,
    },
    success: function (response) {
      if (response.success) {
        console.log(response.data);
      }
    },
    error: function () {
      console.log('AJAX request failed');
    },
  });
});
