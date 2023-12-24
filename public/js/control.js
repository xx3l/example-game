document.addEventListener("keydown", function(event) {
        switch(event.code) {
        case "KeyW":
            sendDirection("N");
            break;
        case "KeyS":
            sendDirection("S");
            break;
        case "KeyA":
            sendDirection("W");
            break;
        case "KeyD":
            sendDirection("E");
            break;
    }
});

function sendDirection(direction) {
    fetch('game.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'direction=' + direction
    })
    .catch(error => {
        console.error('Ошибка:', error);
    });
}
