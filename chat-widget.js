/**
 * Live chat widget for the "Try it yourself" demo section.
 * Plain JS + jQuery, no build step, no external chat library.
 *
 * Backend contract (n8n webhook, do not change):
 *   POST https://automate.teknexuss.com/webhook/furqandev-chat
 *   body: { message: string, sessionId: string }
 *   resp: { reply: string }
 */
(function () {
  'use strict';

  var CHAT_ENDPOINT = 'https://automate.teknexuss.com/webhook/furqandev-chat';
  var SESSION_KEY = 'furqandev_chat_session_id';
  var GREETING = "Hey — I'm the assistant trained on this page. Ask me anything about pricing, timeline, or how this works.";

  var $log, $form, $input, $send;
  var busy = false;

  function getSessionId() {
    var id = null;
    try { id = localStorage.getItem(SESSION_KEY); } catch (e) {}
    if (id) return id;

    id = (window.crypto && typeof window.crypto.randomUUID === 'function')
      ? window.crypto.randomUUID()
      : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
          var r = Math.random() * 16 | 0;
          var v = c === 'x' ? r : (r & 0x3 | 0x8);
          return v.toString(16);
        });

    try { localStorage.setItem(SESSION_KEY, id); } catch (e) {}
    return id;
  }

  function scrollToBottom() {
    $log.scrollTop($log[0].scrollHeight);
  }

  function escapeHtml(text) {
    return $('<div></div>').text(text).html();
  }

  /** Minimal, safe markdown-ish rendering for assistant replies: escape first, then
   *  turn **bold**, "- " bullets and newlines into their HTML equivalents. */
  function formatAssistantText(text) {
    var html = escapeHtml(text);
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/^- (.+)$/gm, '• $1');
    html = html.replace(/\n/g, '<br>');
    return html;
  }

  /** Append a message bubble. role: 'user' | 'assistant' | 'error' */
  function addMessage(role, text) {
    var $msg = $('<div class="chat-msg chat-msg-' + role + '"></div>');
    var $bubble = $('<p class="chat-bubble"></p>');
    if (role === 'assistant') {
      $bubble.html(formatAssistantText(text));
    } else {
      $bubble.text(text);
    }
    $msg.append($bubble);
    $log.append($msg);
    scrollToBottom();
    return $msg;
  }

  function addTypingIndicator() {
    var $msg = $(
      '<div class="chat-msg chat-msg-assistant chat-msg-typing">' +
        '<p class="chat-bubble chat-typing"><span></span><span></span><span></span></p>' +
      '</div>'
    );
    $log.append($msg);
    scrollToBottom();
    return $msg;
  }

  function setBusy(state) {
    busy = state;
    $input.prop('disabled', state);
    $send.prop('disabled', state);
  }

  function sendMessage(text) {
    setBusy(true);
    addMessage('user', text);
    var $typing = addTypingIndicator();

    var sessionId = getSessionId();

    $.ajax({
      url: CHAT_ENDPOINT,
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ message: text, sessionId: sessionId }),
      dataType: 'json',
      timeout: 45000
    }).done(function (res) {
      $typing.remove();
      if (res && typeof res.reply === 'string' && res.reply.trim() !== '') {
        addMessage('assistant', res.reply);
      } else {
        addMessage('error', 'Something went wrong — try again in a moment.');
      }
    }).fail(function () {
      $typing.remove();
      addMessage('error', 'Something went wrong — try again in a moment.');
    }).always(function () {
      setBusy(false);
      $input.trigger('focus');
    });
  }

  function handleSubmit(e) {
    e.preventDefault();
    if (busy) return;
    var text = $input.val().trim();
    if (!text) return;
    $input.val('');
    sendMessage(text);
  }

  $(function () {
    $log = $('#chatLog');
    $form = $('#chatForm');
    $input = $('#chatInput');
    $send = $('#chatSend');

    if (!$log.length || !$form.length) return;

    addMessage('assistant', GREETING);

    $form.on('submit', handleSubmit);

    // Explicit Enter-to-send, in addition to native form submit-on-Enter.
    $input.on('keydown', function (e) {
      if (e.key === 'Enter' || e.which === 13) {
        e.preventDefault();
        $form.trigger('submit');
      }
    });
  });
})();
