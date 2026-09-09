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

  // Contact form — AJAX submit to submit.php
  $('#contactForm').on('submit', function (e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $form.find('button[type="submit"]');
    var $status = $('#formStatus');
    var label = $btn.text();

    $btn.prop('disabled', true).text('Sending…');
    $status.removeClass('is-error is-success').text('');

    $.ajax({
      url: $form.attr('action'),
      method: 'POST',
      dataType: 'json',
      data: $form.serialize()
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
  });

  // Placeholder booking links — replace href with your Calendly link
  $('#bookCallOffer, #bookCallFinal').on('click', function (e) {
    e.preventDefault();
    alert('Add your Calendly link to #bookCallOffer / #bookCallFinal in index.html');
  });
});
