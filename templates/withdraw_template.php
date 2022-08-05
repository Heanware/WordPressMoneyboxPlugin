	<div class="withdraw-request-wrapper">
		<h2 class="dashboard-header">Вывести прибыль?</h2>
		<form class="withdraw-request" action="" method="POST">
			<select name="moneybox_number" id="moneybox-selector">
				<?php foreach ($moneyboxes as $number) { ?>
					<option value="<?php echo $number->id; ?>">
						Копилка <?php echo "№".$number->id;?> 
					</option>
				<?php } 
				if ($referal_bank != 0) { ?>
					<option value="referal">
						Доход с рефералов: <?php echo $referal_bank;?>$ 
					</option>
				<?php }
				?>
			</select>
			<input type="text" name="requisites" id="cardnumber" inputmode="numeric" placeholder="Номер Вашей карты" class="phone-mask" data-mask="999-99-999-9999-9">
			<input type="hidden" name="withdraw">
			<button type="submit" class="user-registration-submit-Button btn button">Отправить запрос</button>
		</form>
	</div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"></script>
	<script>
		jQuery(document).ready(function(){
			jQuery('.phone-mask').mask('+0000-000-0000');
		});
	</script>