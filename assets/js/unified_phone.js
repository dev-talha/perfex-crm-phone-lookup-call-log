(function($){
  'use strict';
  var pageLoadDate = new Date();
  function pad(n){ return n < 10 ? '0' + n : '' + n; }
  function timeFromDate(d){ return pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds()); }
  function currentTimeValue(){ return timeFromDate(new Date()); }
  function pageLoadTimeValue(){ return timeFromDate(pageLoadDate); }
  function currentDateValue(){ var d = new Date(); return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()); }
  function secondsFromTime(value){
    if(!value){ return null; }
    var parts = value.split(':');
    if(parts.length < 2){ return null; }
    return (parseInt(parts[0] || 0, 10) * 3600) + (parseInt(parts[1] || 0, 10) * 60) + parseInt(parts[2] || 0, 10);
  }
  function durationText(seconds){
    seconds = Math.max(0, parseInt(seconds || 0, 10));
    var h = Math.floor(seconds/3600), m = Math.floor((seconds%3600)/60), s = seconds%60;
    return pad(h) + ':' + pad(m) + ':' + pad(s);
  }
  function updateDuration(){
    var form = $('#unified-call-log-form');
    var start = secondsFromTime(form.find('[name="start_time"]').val());
    var end = secondsFromTime(form.find('[name="end_time"]').val());
    if(start === null || end === null){ return; }
    if(end < start){ end += 24 * 3600; }
    form.find('[name="duration_text"]').val(durationText(end - start));
  }
  function applyDefaultTimes(form, force){
    if(!form || !form.length){ return; }
    var type = (form.find('[name="call_type"]').val() || '').toString().toLowerCase();
    if(!form.find('[name="call_date"]').val() || force){ form.find('[name="call_date"]').val(currentDateValue()); }
    if(type === 'incoming'){
      if(!form.find('[name="start_time"]').val() || force){ form.find('[name="start_time"]').val(pageLoadTimeValue()); }
      if(!form.find('[name="end_time"]').val() || force){ form.find('[name="end_time"]').val(currentTimeValue()); }
    } else {
      if(!form.find('[name="start_time"]').val() || force){ form.find('[name="start_time"]').val(currentTimeValue()); }
      if(!form.find('[name="end_time"]').val() || force){ form.find('[name="end_time"]').val(currentTimeValue()); }
    }
    updateDuration();
  }
  function resetRelIdSelect(form){
    var relId = form.find('[name="rel_id"]');
    if(!relId.length){ return; }
    relId.html('');
    if($.fn.selectpicker){ relId.selectpicker('refresh'); }
  }
  function rebuildRelIdSelect(form){
    var relId = form.find('[name="rel_id"]');
    if(!relId.length){ return relId; }
    var selectedVal = relId.val();
    var selectedText = relId.find('option:selected').text();
    try { relId.ajaxSelectPicker('destroy'); } catch(e) {}
    var clone = relId.clone();
    clone.html('');
    if(selectedVal){ clone.append($('<option>', {value: selectedVal, text: selectedText || ('#' + selectedVal), selected: true})); }
    if(relId.selectpicker){ try { relId.selectpicker('destroy'); } catch(e) {} }
    relId.replaceWith(clone);
    if($.fn.selectpicker){ clone.selectpicker('refresh'); }
    return clone;
  }
  function initUnifiedRelatedSearch(form){
    var relType = form.find('[name="rel_type"]').val();
    var relId = rebuildRelIdSelect(form);
    if(!relId.length){ return; }
    if(!relType){ resetRelIdSelect(form); return; }
    if(typeof init_ajax_search === 'function'){
      init_ajax_search(relType, relId, undefined, admin_url + 'unified_phone/related_search');
    }
  }
  function fillCallLogModal(params){
    var modal = $('#unifiedCallLogModal').first();
    var form = modal.find('#unified-call-log-form');
    if(!form.length){ return; }
    params = params || {};
    if(params.phone){ form.find('[name="phone_raw"]').val(params.phone); }
    if(params.call_type !== undefined){ form.find('[name="call_type"]').val(params.call_type || ''); }
    if(params.call_date){ form.find('[name="call_date"]').val(params.call_date); }
    if(params.start_time){ form.find('[name="start_time"]').val(params.start_time); }
    if(params.end_time){ form.find('[name="end_time"]').val(params.end_time); }
    if(params.rel_type){ form.find('[name="rel_type"]').val(params.rel_type); }
    if(params.rel_id){
      var rel = form.find('[name="rel_id"]');
      rel.html('').append($('<option>', {value: params.rel_id, text: params.rel_label || ('#' + params.rel_id), selected: true}));
    }
    if($.fn.selectpicker){ form.find('select.selectpicker').selectpicker('refresh'); }
    applyDefaultTimes(form, !params.start_time || !params.end_time);
    initUnifiedRelatedSearch(form);
  }
  function openCallLogModal(params){
    var modal = $('#unifiedCallLogModal').first();
    if(!modal.length){ return; }
    fillCallLogModal(params);
    modal.modal('show');
  }
  function dialMicroSip(phone){
    phone = (phone || '').toString().replace(/[^0-9+]/g, '');
    if(!phone){ return; }
    var scheme = window.unifiedPhoneSipScheme || 'sip';
    window.location.href = scheme + ':' + encodeURIComponent(phone);
  }
  window.unifiedPhoneStartSipCall = function(phone, options){
    options = options || {};
    phone = (phone || '').toString().trim();
    if(!phone){ return; }
    var now = currentTimeValue();
    var params = $.extend({
      phone: phone,
      call_type: 'outgoing',
      call_date: currentDateValue(),
      start_time: now,
      end_time: now
    }, options || {});
    openCallLogModal(params);
    setTimeout(function(){ dialMicroSip(phone); }, 120);
  };
  function getIdFromSelector(selectors){
    for(var i=0;i<selectors.length;i++){
      var v = $(selectors[i]).first().val() || $(selectors[i]).first().data('id');
      if(v && /^\d+$/.test(v.toString())){ return v.toString(); }
    }
    return '';
  }
  function detectRelatedContextFromElement(el){
    var $el = $(el || []);
    var type = ($el.data('rel-type') || $el.attr('data-rel-type') || '').toString().toLowerCase();
    var id = ($el.data('rel-id') || $el.attr('data-rel-id') || '').toString();
    if(type && id){ return {rel_type:type, rel_id:id, rel_label:$el.data('rel-label') || $el.attr('data-rel-label') || ''}; }
    var $ctx = $el.closest('[data-rel-type][data-rel-id], [data-unified-rel-type][data-unified-rel-id]');
    if($ctx.length){
      return {rel_type:($ctx.data('rel-type') || $ctx.data('unified-rel-type') || '').toString().toLowerCase(), rel_id:($ctx.data('rel-id') || $ctx.data('unified-rel-id') || '').toString(), rel_label:$ctx.data('rel-label') || $ctx.data('unified-rel-label') || ''};
    }
    return null;
  }
  function findIdInTableRow($row, relType){
    if(!$row || !$row.length){ return ''; }
    var hrefPattern = relType === 'lead' ? /\/admin\/leads\/index\/(\d+)/ : /\/admin\/clients\/client\/(\d+)/;
    var found = '';
    $row.find('a[href]').each(function(){
      var href = $(this).attr('href') || '';
      var m = href.match(hrefPattern);
      if(m && m[1]){ found = m[1]; return false; }
    });
    if(found){ return found; }
    var cb = $row.find('input[type="checkbox"][value]').first().val();
    if(cb && /^\d+$/.test(cb.toString())){ return cb.toString(); }
    var cells = $row.children('td');
    var candidate = $.trim(cells.eq(1).text() || cells.eq(0).text() || '');
    var m2 = candidate.match(/\d+/);
    return m2 ? m2[0] : '';
  }
  function detectListTableContext(el){
    var path = window.location.pathname || '';
    var relType = '';
    if(/\/admin\/leads(?:\/|$)/.test(path)){ relType = 'lead'; }
    else if(/\/admin\/clients(?:\/|$)/.test(path)){ relType = 'customer'; }
    if(!relType){ return null; }
    var $row = $(el).closest('tr');
    var relId = findIdInTableRow($row, relType);
    if(!relId){ return null; }
    return {rel_type: relType, rel_id: relId, rel_label: relType.charAt(0).toUpperCase() + relType.slice(1) + ' #' + relId};
  }
  function detectRelatedContext(el){
    var byElement = detectRelatedContextFromElement(el);
    if(byElement && byElement.rel_type && byElement.rel_id){ return byElement; }
    var byTable = detectListTableContext(el);
    if(byTable && byTable.rel_type && byTable.rel_id){ return byTable; }
    var path = window.location.pathname || '';
    var ctx = {rel_type:'', rel_id:''};
    var patterns = [
      ['lead', /\/admin\/leads\/(?:index|lead)\/(\d+)/],
      ['customer', /\/admin\/clients\/client\/(\d+)/],
      ['project', /\/admin\/projects\/(?:view|project)\/(\d+)/],
      ['invoice', /\/admin\/invoices\/(?:list_invoices|invoice)\/(\d+)/],
      ['estimate', /\/admin\/estimates\/(?:list_estimates|estimate)\/(\d+)/],
      ['proposal', /\/admin\/proposals\/(?:index|list_proposals|proposal|pipeline_open)\/(\d+)/],
      ['contract', /\/admin\/contracts\/contract\/(\d+)/],
      ['ticket', /\/admin\/tickets\/ticket\/(\d+)/]
    ];
    for(var i=0;i<patterns.length;i++){
      var m = path.match(patterns[i][1]);
      if(m){ ctx.rel_type = patterns[i][0]; ctx.rel_id = m[1]; break; }
    }
    var leadModal = $('#lead-modal:visible');
    if(leadModal.length && leadModal.find('input[name="leadid"]').val()){
      ctx.rel_type='lead'; ctx.rel_id=leadModal.find('input[name="leadid"]').val();
    }
    if(!ctx.rel_id){
      var pageMap = [
        ['lead', ['input[name="leadid"]','#leadid','input[name="id"][data-rel-type="lead"]']],
        ['proposal', ['input[name="proposalid"]','input[name="proposal_id"]']],
        ['invoice', ['input[name="invoiceid"]','input[name="invoice_id"]']],
        ['estimate', ['input[name="estimateid"]','input[name="estimate_id"]']],
        ['project', ['input[name="project_id"]','input[name="projectid"]']],
        ['contract', ['input[name="contractid"]','input[name="contract_id"]']],
        ['ticket', ['input[name="ticketid"]','input[name="ticket_id"]']],
        ['customer', ['input[name="userid"]','input[name="clientid"]']]
      ];
      for(var j=0;j<pageMap.length;j++){
        var found = getIdFromSelector(pageMap[j][1]);
        if(found){ ctx.rel_type = pageMap[j][0]; ctx.rel_id = found; break; }
      }
    }
    return ctx;
  }
  function normalizePhoneText(text){ return (text || '').toString().replace(/[^0-9+]/g, ''); }

  $(document).on('click', '#unified-sip-call-btn', function(){
    var phone = $('#unified-sip-phone').val();
    window.unifiedPhoneStartSipCall(phone, {call_type:'outgoing'});
  });
  $(document).on('keypress', '#unified-sip-phone', function(e){ if(e.which === 13){ $('#unified-sip-call-btn').trigger('click'); } });
  $(document).on('click', '.unified-dial-key', function(){
    var key = ($(this).attr('data-key') || '').toString();
    var input = $('#unified-sip-phone');
    var val = input.val() || '';
    if(key === '⌫'){ input.val(val.slice(0, -1)); }
    else { input.val(val + key); }
    input.focus();
  });

  $(document).on('shown.bs.modal', '#unifiedCallLogModal', function(){
    var form = $('#unified-call-log-form');
    applyDefaultTimes(form, true);
    form.find('select.selectpicker').selectpicker('refresh');
    initUnifiedRelatedSearch(form);
  });
  $(document).on('change', '#unified-call-log-form [name="call_type"]', function(){ applyDefaultTimes($('#unified-call-log-form'), true); });
  $(document).on('change', '#unified-call-log-form [name="start_time"], #unified-call-log-form [name="end_time"]', updateDuration);
  $(document).on('change changed.bs.select', '#unified-call-log-form [name="rel_type"]', function(){ initUnifiedRelatedSearch($('#unified-call-log-form')); });
  $(document).ready(function(){
    var form = $('#unified-call-log-form');
    if(form.length){ initUnifiedRelatedSearch(form); updateDuration(); }
    if($.fn.tooltip){ $('[data-toggle="tooltip"]').tooltip({container: 'body'}); }
  });
  function validateRecordingInput(form){
    var input = form.find('input[type="file"][name="call_recording"]')[0];
    if(!input || !input.files || !input.files.length){ return true; }
    var file = input.files[0];
    var maxKb = parseInt($(input).data('max-kb') || 0, 10);
    var allowed = ($(input).data('allowed-types') || '').toString().toLowerCase().split(',').map(function(v){ return $.trim(v); }).filter(Boolean);
    var ext = (file.name.split('.').pop() || '').toLowerCase();
    if(maxKb > 0 && file.size > maxKb * 1024){
      alert('The selected recording is larger than the allowed size (' + maxKb + ' KB).');
      input.value = '';
      return false;
    }
    if(allowed.length && $.inArray(ext, allowed) === -1){
      alert('This recording file type is not allowed. Allowed types: ' + allowed.join(', '));
      input.value = '';
      return false;
    }
    return true;
  }
  $(document).on('change', 'input[type="file"][name="call_recording"]', function(){ validateRecordingInput($(this).closest('form')); });
  $(document).on('submit', '#unified-call-log-form', function(e){
    var form = $(this);
    var isAjaxModal = form.closest('.modal').length > 0;
    applyDefaultTimes(form, false);
    if(!validateRecordingInput(form)){ e.preventDefault(); return false; }
    if(!isAjaxModal){ return true; }
    e.preventDefault();
    var fd = new FormData(form[0]);
    $.ajax({url: form.attr('action'), type: 'POST', data: fd, processData: false, contentType: false}).done(function(resp){
      try { resp = JSON.parse(resp); } catch(e) {}
      if(resp.success){ window.location.reload(); } else { alert(resp.message || 'Unable to save call log.'); }
    }).fail(function(xhr){ alert((xhr && xhr.responseText) ? xhr.responseText.replace(/<[^>]+>/g, ' ').trim().slice(0, 220) : 'Unable to save call log.'); });
  });

  $(document).on('click', 'a[href^="tel:"], a[href^="sip:"], a[href^="callto:"], .unified-global-click-to-call', function(e){
    if(!window.unifiedPhoneGlobalClickToCall){ return; }
    var href = $(this).attr('href') || '';
    var phone = $(this).data('phone') || href.replace(/^(tel|sip|callto):/i, '') || $(this).text();
    phone = normalizePhoneText(phone);
    if(!phone){ return; }
    e.preventDefault();
    var ctx = detectRelatedContext(this);
    window.unifiedPhoneStartSipCall(phone, ctx);
  });
})(jQuery);

(function($){
  'use strict';

  function getLeadIdFromModal(){
    var $leadModal = $('#lead-modal');
    if(!$leadModal.length){ return ''; }
    var leadId = $leadModal.find('input[name="leadid"]').first().val();
    return leadId || '';
  }

  function ensureLeadCallLogTab(){
    var $leadModal = $('#lead-modal');
    if(!$leadModal.length || !$leadModal.is(':visible')){ return; }

    var leadId = getLeadIdFromModal();
    if(!leadId){ return; }

    var $tabs = $leadModal.find('.top-lead-menu ul.nav-tabs[role="tablist"]').first();
    if(!$tabs.length){ $tabs = $leadModal.find('ul.nav-tabs[role="tablist"]').first(); }
    if(!$tabs.length){ return; }

    var $content = $leadModal.find('.modal-body .tab-content').first();
    if(!$content.length){ $content = $leadModal.find('.tab-content').first(); }
    if(!$content.length){ return; }

    var tabId = 'tab_unified_phone_call_logs';
    var tabHref = '#' + tabId;
    var $link = $tabs.find('a[href="' + tabHref + '"]').first();

    if(!$link.length){
      $tabs.append('<li role="presentation" class="unified-phone-lead-tab"><a href="' + tabHref + '" aria-controls="' + tabId + '" role="tab" data-toggle="tab">Call Log</a></li>');
      $link = $tabs.find('a[href="' + tabHref + '"]').first();
    } else {
      $link.html('Call Log');
      $link.closest('li').addClass('unified-phone-lead-tab');
    }

    var $pane = $content.find(tabHref).first();
    if(!$pane.length){
      $content.append('<div role="tabpanel" class="tab-pane" id="' + tabId + '"><div class="text-muted p-3">Loading...</div></div>');
      $pane = $content.find(tabHref).first();
    }

    var loadLogs = function(){
      var currentLeadId = getLeadIdFromModal();
      if(!currentLeadId){ return; }
      var loadedFor = $pane.attr('data-unified-loaded-for');
      if(loadedFor == currentLeadId){ return; }
      $pane.attr('data-unified-loaded-for', currentLeadId);
      $pane.html('<div class="text-muted p-3">Loading...</div>');
      $pane.load(admin_url + 'unified_phone/lead_call_logs_tab/' + currentLeadId);
    };

    $link.off('shown.bs.tab.unifiedPhoneLead').on('shown.bs.tab.unifiedPhoneLead', loadLogs);
    $link.off('click.unifiedPhoneLead').on('click.unifiedPhoneLead', function(){ setTimeout(loadLogs, 50); });

    if(typeof init_tabs_scrollable === 'function'){
      setTimeout(function(){ init_tabs_scrollable(); }, 100);
    }
  }

  window.unifiedPhoneEnsureLeadCallLogTab = ensureLeadCallLogTab;

  $(document).on('shown.bs.modal', '#lead-modal', function(){
    setTimeout(ensureLeadCallLogTab, 150);
    setTimeout(ensureLeadCallLogTab, 600);
    setTimeout(ensureLeadCallLogTab, 1200);
  });

  $(document).ajaxComplete(function(event, xhr, settings){
    if(settings && settings.url && settings.url.indexOf('leads/lead') !== -1){
      setTimeout(ensureLeadCallLogTab, 200);
      setTimeout(ensureLeadCallLogTab, 800);
    }
  });

  $(function(){
    if($('#lead-modal').length && window.MutationObserver){
      var target = document.getElementById('lead-modal');
      var observer = new MutationObserver(function(){
        if($('#lead-modal').is(':visible')){
          setTimeout(ensureLeadCallLogTab, 100);
        }
      });
      observer.observe(target, {childList:true, subtree:true});
    }
  });
})(jQuery);
