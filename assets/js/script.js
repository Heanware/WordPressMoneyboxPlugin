function copyToClickboard(text) {
	let tmp   = document.createElement('INPUT'), // Создаём новый текстовой input
        focus = document.activeElement; // Получаем ссылку на элемент в фокусе (чтобы не терять фокус)
	tmp.value = text; // Временному input вставляем текст для копирования

	document.body.appendChild(tmp); // Вставляем input в DOM
    tmp.select(); // Выделяем весь текст в input
    document.execCommand('copy'); // Магия! Копирует в буфер выделенный текст (см. команду выше)
    document.body.removeChild(tmp); // Удаляем временный input
    let temp = document.body.getElementsByClassName('user-registration-MyAccount-navigation')[0];
    let notice = document.getElementById('ur-submit-message-node');
    if(!notice){
    	temp.insertAdjacentHTML('afterend', '<div class="ur-message user-registration-message cabinet-notice-moneybox" id="ur-submit-message-node"><ul class=""><li>Ссылка скопирована в буфер!</li></ul></div>');
    }

}