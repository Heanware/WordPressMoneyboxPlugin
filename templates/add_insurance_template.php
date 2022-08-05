<?php
if (count($moneyboxes) != 0) { ?>
	<h2 class="dashboard-header">Застраховать копилку?</h2>
	<div class="withdraw-request-wrapper">
		<form class="withdraw-request insurance-request" action="" method="POST">
			<select name="moneybox_number" id="insurance-selector">
				<?php $type = "";
				foreach ($moneyboxes as $moneybox) {
					switch ($moneybox->moneybox_id) {
						case 1:
						case 4:
						$type = "Любопытная";
						break;
						case 2:
						case 5:
						$type = "Оптимальная";
						break;
						case 3:
						case 6:
						$type = "Жадная";
						break;
						case 12:
						$type = "Тестовая";
						break;
						default:
						$type = "";
						break;
					}?>
					<option value="<?php echo $moneybox->id; ?>">
						Копилка <?php echo "№".$moneybox->id;?> <?php echo "(".$type.")";?>
					</option>
				<?php } ?>
			</select>
			<input type="hidden" name="insurance">
			<button type="submit" class="user-registration-submit-Button btn button">Застраховать копилку</button>
		</form>
	</div>
<?php } else { ?>
	<h2 class="tag-replace">Нечего застраховать!</h2>
	<?php } ?>