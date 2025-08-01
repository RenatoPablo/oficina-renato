document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
        let alerts = document.querySelectorAll('.alert');
        alerts.forEach(function (alert) {
            let bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        });
    }, 7000);
});
