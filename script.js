// reCAPTCHA v3 site key — public by design, safe to hardcode client-side.
// Get one at https://www.google.com/recaptcha/admin/create (register furqandev.com).
var RECAPTCHA_SITE_KEY = 'YOUR_RECAPTCHA_V3_SITE_KEY';

$(function () {
  // Mobile nav toggle
  $('#navToggle').on('click', function () {
    var open = $('#navLinks').toggleClass('open').hasClass('open');
    $(this).attr('aria-expanded', open);
  });
  $('.nav-link').on('click', function () {
    $('#navLinks').removeClass('open');
    $('#navToggle').attr('aria-expanded', false);
  });

  // FAQ accordion (one open at a time)
  $('.faq-question').on('click', function () {
    var $item = $(this).closest('.faq-item');
    var $answer = $item.find('.faq-answer');
    var isOpen = $(this).attr('aria-expanded') === 'true';

    $('.faq-question').attr('aria-expanded', 'false');
    $('.faq-answer').css('max-height', '0px');

    if (!isOpen) {
      $(this).attr('aria-expanded', 'true');
      $answer.css('max-height', $answer[0].scrollHeight + 'px');
    }
  });

  // Contact form — AJAX submit to submit.php, gated by reCAPTCHA v3
  $('#contactForm').on('submit', function (e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $form.find('button[type="submit"]');
    var $status = $('#formStatus');
    var label = $btn.text();

    $btn.prop('disabled', true).text('Sending…');
    $status.removeClass('is-error is-success').text('');

    function fail(msg) {
      $status.addClass('is-error').text(msg);
      $btn.prop('disabled', false).text(label);
    }

    if (typeof grecaptcha === 'undefined') {
      fail('Verification failed to load. Please refresh the page and try again.');
      return;
    }

    function submitWithToken(token) {
      var data = $form.serializeArray();
      data.push({ name: 'recaptcha_token', value: token });

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        dataType: 'json',
        data: $.param(data)
      }).done(function (res) {
        if (res && res.success) {
          $form[0].reset();
          $status.addClass('is-success').text(res.message || 'Thanks — I\'ll be in touch shortly.');
        } else {
          $status.addClass('is-error').text((res && res.message) || 'Something went wrong. Please try again.');
        }
      }).fail(function (xhr) {
        var msg = 'Something went wrong. Please try again.';
        try {
          var r = JSON.parse(xhr.responseText);
          if (r && r.message) { msg = r.message; }
        } catch (err) {}
        $status.addClass('is-error').text(msg);
      }).always(function () {
        $btn.prop('disabled', false).text(label);
      });
    }

    // grecaptcha.execute() can both reject its promise AND throw synchronously
    // (e.g. an invalid/unconfigured site key) — guard both so the button never
    // gets stuck on "Sending…".
    try {
      grecaptcha.ready(function () {
        try {
          grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: 'contact_form' })
            .then(submitWithToken)
            .catch(function () { fail('Verification failed. Please try again.'); });
        } catch (err) {
          fail('Verification failed. Please try again.');
        }
      });
    } catch (err) {
      fail('Verification failed. Please try again.');
    }
  });

  // Placeholder booking links — replace href with your Calendly link
  $('#bookCallOffer, #bookCallFinal').on('click', function (e) {
    e.preventDefault();
    alert('Add your Calendly link to #bookCallOffer / #bookCallFinal in index.html');
  });
});
