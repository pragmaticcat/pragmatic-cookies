(function() {
  'use strict';

  // Find rows with running or pending scans
  var rows = document.querySelectorAll('tr[data-scan-id]');
  var pollingScanIds = [];

  rows.forEach(function(row) {
    var statusEl = row.querySelector('.scan-status');
    if (!statusEl) return;
    var text = statusEl.textContent.trim().toLowerCase();
    if (text.indexOf('running') !== -1 || text.indexOf('pending') !== -1) {
      pollingScanIds.push(row.dataset.scanId);
    }
  });

  if (pollingScanIds.length === 0) return;

  function pollStatus() {
    pollingScanIds.forEach(function(scanId) {
      Craft.sendActionRequest('GET', 'pragmatic-cookies/scan/status', {
        params: { scanId: scanId }
      }).then(function(response) {
        var data = response.data;
        var row = document.querySelector('tr[data-scan-id="' + scanId + '"]');
        if (!row) return;

        var statusEl = row.querySelector('.scan-status');
        var pagesEl = row.querySelector('.scan-pages');
        var cookiesEl = row.querySelector('.scan-cookies');

        if (pagesEl) pagesEl.textContent = data.pagesScanned;
        if (cookiesEl) cookiesEl.textContent = data.cookiesFound;

        if (data.status === 'completed') {
          if (statusEl) {
            statusEl.innerHTML = '<span class="status on"></span> Completed';
          }
          // Add view results link
          var lastTd = row.querySelector('td:last-child');
          if (lastTd && !lastTd.querySelector('a')) {
            lastTd.innerHTML = '<a href="' + Craft.getCpUrl('pragmatic-cookies/scanner/results/' + scanId) + '" class="btn small">View Results</a>';
          }
          // Remove from polling
          var idx = pollingScanIds.indexOf(scanId);
          if (idx > -1) pollingScanIds.splice(idx, 1);
        } else if (data.status === 'failed') {
          if (statusEl) {
            statusEl.innerHTML = '<span class="status off"></span> Failed';
          }
          var idx2 = pollingScanIds.indexOf(scanId);
          if (idx2 > -1) pollingScanIds.splice(idx2, 1);
        } else if (data.status === 'running') {
          if (statusEl) {
            statusEl.innerHTML = '<span class="status"></span> Running <span class="scan-progress">(' + data.pagesScanned + '/' + data.totalPages + ')</span>';
          }
        }
      });
    });

    if (pollingScanIds.length > 0) {
      setTimeout(pollStatus, 3000);
    }
  }

  // Start polling
  setTimeout(pollStatus, 2000);
})();
