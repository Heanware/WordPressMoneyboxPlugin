	<?php if(count($user_bids) != 0){ ?>
		<div class="active-bids">
			<h2>Ваши активные заявки</h2>
			<table class="admin-table">
				<tr>
					<td>Номер копилки</td>
					<td>Сумма</td>
					<td>Номер телефона</td>
				</tr>
				<?php foreach ($user_bids as $bid) {?>
					<tr>
						<td><?php echo $bid['moneybox_id'] ?></td>
						<td><?php echo $bid['total'] ?>$</td>
						<td><?php echo $bid['cardnumber'] ?></td>
					</tr>
				<?php } ?>
			</table>
		</div>
	<?php }