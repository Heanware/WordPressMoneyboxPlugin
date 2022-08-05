	<div class="admin-send-profit">
		<h1>Админ-панель</h1>
		<form class="admin-send-profit" action="" method="POST">
			<input type="text" name="profit" placeholder="Тут количество заработанных шекелей (!!!ДРОБИ ЧЕРЕЗ ТОЧКУ!!!)" id="profit">
			<button class="user-registration-submit-Button btn button">Отправить прибыль</button>
			<div class="admin-wrapper">
				<input type="submit" name="redact" id="redact" value="Сбросить введённые значения (обнулить предыдущий ввод)">
			</div>
		</form>
		<form action="" method="POST" class="admin-support">
			<div class="admin-wrapper jackpot-wrapper"> 
				<input type="text" name="support"  placeholder="ID пользователя">
				<select name="type">
					<option value="jackpot">Джекпот</option>
					<option value="bonus">Бонус</option>
					<option value="support">Поддержка</option>
				</select>
				<input type="submit" name="jackpot_add" value="Зачислить джекпот">
			</div>
		</form>
	</div>
	<table class="admin-table admin-results">
		<h1>Наши результаты</h1>
		<tr>
			<td>Всего:</td>
			<td><?php echo $invested ?></td>
		</tr>
		<tr>
			<td>Доля пользователей:</td>		
			<td><?php echo $users ?></td>
		</tr>
		<tr>
			<td>Реферальные:</td>
			<td><?php echo $referal ?></td>
		</tr>
		<tr>
			<td>Страховка:</td>
			<td><?php echo $insurance ?></td>
		</tr>
		<tr>
			<td>Джекпот</td>
			<td><?php echo $support['jackpot'] ?></td>
		</tr>
		<tr>
			<td>Бонус</td>
			<td><?php echo $support['bonus'] ?></td>
		</tr>
		<tr>
			<td>Поддержка</td>
			<td><?php echo $support['support'] ?></td>
		</tr>
		<tr>
			<td>Наше:</td>
			<td><?php echo $personal ?></td>
		</tr>
		<tr>
			<td>
				<form action="" method="POST">
					<input type="hidden" name="admin_our_profit" value="0">
					<button class="user-registration-submit-Button" >Обнулить</button>
				</form>
			</td>
		</tr>
	</table>
	<table class="admin-table">
		<tr>
			<th>ID пользователя</th>
			<th>Номер копилки</th>
			<th>Итог</th>
			<th>Номер телефона</th>
			<th>Подтвердить перевод</th>
			<th>Отклонить вывод</th>
		</tr>
				<?php // Добавить в таблицу поле для отклонения заявки
				foreach ($requests as $request) { ?>
					<tr>
						<td><?php echo $request->user_id ?></td>
						<td><?php echo $request->moneybox_id ?></td>
						<td><?php echo $request->total.'$'?></td>
						<td class="cardnumber"><?php echo $request->cardnumber ?></td>
						<td>
							<form action="" method="POST">
								<input type="hidden" name="admin_delete_withdraw" value="<?php echo $request->moneybox_id ?>">
								<button class="user-registration-submit-Button">Подтвердить</button>
							</form>
						</td>
						<td>
							<form action="" method="POST">
								<input type="hidden" name="admin_cancel_withdraw" value="<?php echo $request->moneybox_id ?>">
								<button class="user-registration-submit-Button">Отклонить</button>
							</form>
						</td> 
					</tr>
				<?php } ?>
			</table>