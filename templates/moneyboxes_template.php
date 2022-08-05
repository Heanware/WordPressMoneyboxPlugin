<script src="<?php echo plugins_url('../assets/js/slider.js', __FILE__)?>"></script>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		const slider = new ChiefSlider('.slider', {
			loop: false,
			swipe: true
		});
	});
</script>
<div class="slider">
	<div class="slider__moneybox-container">
		<div class="slider__wrapper">
			<div class="slider__items">
				<?php foreach ($moneyboxes as $moneybox) {?>
					<div class="slider__item moneybox-container" style="background-image: url('<?php echo $moneybox['dir'] ?>')">
						<div class="maininfo"><br>
							<span>№<?php echo $moneybox['number']; ?></span><br>
							<span>Вложено: <?php echo $moneybox['amount']; ?>$ </span><br>
							<span>Результат: 
								<?php
								$total;
								if ($moneybox['insurance'] == 0 || $moneybox['amount'] + $moneybox['profit_week'] > $moneybox['amount']){
									$total = $moneybox['amount'] + $moneybox['profit_week'];
									echo round($total, 2);
								} else {
									$total = $moneybox['amount'];
									echo round($total, 2);
								}?>$
							</span>
						</div>
						<div class="overlay">
							<div class="full-info">	<span><?php echo $moneybox['type'] ?></span>
								<span><?php echo $moneybox['broken'] ?></span>
								<span><?php echo $moneybox['isInsurance'] ?></span>
								<span>Куплена: <?php echo $moneybox['date'] ?></span>
								<span>Вывод с: <?php echo $moneybox['min_interval'] ?></span>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
	<a href="#" class="slider__control" data-slide="prev"></a>
	<a href="#" class="slider__control" data-slide="next"></a>
</div>