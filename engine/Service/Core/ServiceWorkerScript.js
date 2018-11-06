(function (w, n, d) {
  function showRefreshUI(registration) {
    var button = d.createElement('button');
    button.className = 'button is-primary is-warning';
    button.textContent = 'This site has updated. Please click to see changes.';

    button.addEventListener('click', function () {
      if (!registration.waiting) return;

      button.disabled = true;

      registration.waiting.postMessage('skipWaiting');
    });

    d.body.appendChild(button);
  }

  function onServiceWorker(registration, callback) {
    function onStateChange() {
      registration.installing.addEventListener('statechange', function (event) {
        if (event.target.state === 'installed') callback();
      });
    }

    if (registration.waiting) return callback();
    if (registration.installing) return onStateChange();

    registration.addEventListener('updatefound', onStateChange);
  }

  if ('serviceWorker' in n) {
    w.addEventListener('load', function () {
      n.serviceWorker.register('{{script}}', {scope: '/'})
        .then(function (registration) {
          if (!n.serviceWorker.controller) return;

          var reloading;
          n.serviceWorker.addEventListener('controllerchange', function (event) {
            if (reloading) return;
            reloading = true;
            w.location.reload();
          });

          onServiceWorker(registration, function () {
            showRefreshUI(registration);
          });
        });
    });
  }
}(window, navigator, document));
