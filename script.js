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

  // Placeholder booking links — replace href with your Calendly link
  $('#bookCallOffer, #bookCallFinal').on('click', function (e) {
    e.preventDefault();
    alert('Add your Calendly link to #bookCallOffer / #bookCallFinal in index.html');
  });
});
