let lastWithdrawAlertTime = 0;
let lastChainReqAlertTime = 0;
function checkWithdrawOrders() {
    return fetch('/api/v1/check-withdraw-alert')
        .then(response => response.json())
        .then(result => {
            const now = Date.now();

            if (result.data.should_alert && now - lastWithdrawAlertTime > 60000) { // 限制1分钟内只提醒一次
                let audio = new Audio('/nova/sounds/tixian.mp3');
                audio.play();
                lastWithdrawAlertTime = now;
            }
        });
}

function checkChainReqOrders() {
    return fetch('/api/v1/check-chainreq-alert')
        .then(response => response.json())
        .then(result => {
            const now = Date.now();
            
            if (result.data.should_alert && now - lastChainReqAlertTime > 60000) {
                let audio = new Audio('/nova/sounds/chuzhi.mp3');
                audio.play();
                lastChainReqAlertTime = now;
            }
        });
}

function checkUserRealName() {
    return fetch('/api/v1/check-realname-alert')
        .then(response => response.json())
        .then(result => {
            const now = Date.now();
            
            if (result.data.should_alert && now - lastChainReqAlertTime > 60000) {
                let audio = new Audio('/nova/sounds/sfrz.mp3');
                audio.play();
                lastChainReqAlertTime = now;
            }
        });
}

// 串行轮询
function pollAlerts() {
    checkWithdrawOrders().then(() => {
        setTimeout(() => {
            checkChainReqOrders();
            setTimeout(() => {
                checkUserRealName();
            }, 5000);
        }, 5000);
    });
}
pollAlerts();
setInterval(pollAlerts, 15000);