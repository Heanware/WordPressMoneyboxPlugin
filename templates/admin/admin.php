
<table class="main_admin_table">
	<caption>Admin interaction (USE CAREFULLY)</caption>
	<tr>
		<td>Прибыль</td>
		<form method="POST">
			<td colspan="2">
				<input type="text" placeholder="ДРОБНЫЕ ЧЕРЕЗ ТОЧКУ!" name="amount">
			</td>
			<td>
				<input type="submit" value="Ввести">
				<input type="hidden" name="value" value="profit">
			</td>
		</form>
	</tr>
	<tr>
		<td>Зачисление</td>
		<form method="POST">
			<td>		
				<select name="option">
					<option value="jackpot">Джекпот</option>
					<option value="bonus">Бонус</option>
					<option value="support">Поддержка</option>
				</select>
			</td>
			<td><input type="text" placeholder="User ID" name="id"></td>
			<td>
				<input type="submit">
				<input type="hidden" name="value" value="jackpot">
			</td>
		</form>
	</tr>
	<tr>
		<td colspan="3">Cброс прибыли</td>
		<td>
			<form method="POST">
				<input type="submit" value="Сбросить">
				<input type="hidden" name="value" value="reset">
			</form>
		</td>
	</tr>
	<tr>
		<td colspan="3">Обнуление нашего дохода</td>
		<td>
			<form method="POST">
				<input type="submit" value="Обнулить">
				<input type="hidden" name="value" value="our">
			</form>
		</td>
	</tr>
</table>
<?php 
		/*
		Форма ввода и сброса прибыли
		Зачисление джекпота
		*/ 
		?>
		<div class="tables_wrapper">
			<table class="panel_table">
				<caption>Users</caption>
				<tr>
					<th>ID</th>
					<th>Login</th>
					<th>E-mail</th>
					<th>Referal bank</th>
				</tr>
				<?php foreach ($data['users'] as $user) { ?>
					<tr>
						<td><?= $user->ID ?></td>
						<td><?= $user->user_login ?></td>
						<td><?= $user->user_email ?></td>
						<td><?= $user->referal_bank ?></td>
					</tr>
				<?php } ?>
			</table>
			<table class="admin-table admin-results">
				<caption>Наши результаты</caption>
				<tr>
					<td>Всего:</td>
					<td><?= $data['invested'] ?></td>
				</tr>
				<tr>
					<td>Доля пользователей:</td>		
					<td><?= $data['userspart'] ?></td>
				</tr>
				<tr>
					<td>Реферальные:</td>
					<td><?= $data['referal'] ?></td>
				</tr>
				<tr>
					<td>Страховка:</td>
					<td><?= $data['insurance'] ?></td>
				</tr>
				<tr>
					<td>Джекпот</td>
					<td><?= $data['support']['jackpot'] ?></td>
				</tr>
				<tr>
					<td>Бонус</td>
					<td><?= $data['support']['bonus'] ?></td>
				</tr>
				<tr>
					<td>Поддержка</td>
					<td><?= $data['support']['support'] ?></td>
				</tr>
				<tr>
					<td>Наше:</td>
					<td><?= $data['personal'] ?></td>
				</tr>
			</table>
			<table class="panel_table">
				<caption>Weeks</caption>
				<tr>
					<th>Monday date</th>
					<th>Startbank</th>
					<th>Endbank</th>
					<th>Percentage</th>
					<th>Insurance bank</th>
				</tr>
				<?php foreach ($data['weeks'] as $key) { ?>
					<tr>
						<td><?= $key->date?></td>
						<td><?= $key->startbank?></td>
						<td><?= $key->endbank ?></td>
						<td><?= $key->percent ?></td>
						<td><?= $key->insurance ?></td>
					</tr>
				<?php } ?>
			</table>
		</div>
		<div class="tables_wrapper">
			<table class="panel_table">
				<caption>Withdraw requests</caption>
				<tr>
					<th>Login</th>
					<th>Amount</th>
					<th>Requisites</th>
					<th>Action</th>
					<th>Confirm</th>
				</tr>

				<?php foreach ($data['requests'] as $request) {?>
					<tr>
						<td><?= $request->user_login ?></td>
						<td><?= $request->total ?></td>
						<td><?= $request->cardnumber ?></td>
						<form method="POST">
							<td>
								<select name="option">
									<option value="1">Accept</option>
									<option value="0">Cancel</option>
								</select>
							</td>
							<td>
								<input type="submit" name="request">
								<input type="hidden" name="id" value="<?= $request->moneybox_id ?>">
								<input type="hidden" name="value" value="request">
							</td>
						</form>
					</tr>
				<?php } ?>
			</table>
			<table class="panel_table" id="panel_moneyboxes">
				<caption>Moneyboxes</caption>
				<tr>
					<th>ID</th>	
					<th>Login</th>
					<th>Type</th>
					<th>Amount</th>
					<th>Profit</th>
					<th>Weekly</th>
					<th>Broken?</th>
					<th>Insured?</th>
					<th>Bought</th>
					<th>Exp. date</th>
				</tr>
				<?php
				$c = "Любопытная"; // Особо не было смысла это делать, просто захотелось прилепить перевод.
				$o = "Оптимальная";
				$g = "Жадная";
				$t;
				foreach ($data['moneyboxes'] as $moneybox) { ?>
					<tr>
						<td><?= $moneybox->id ?></td>
						<td><?= $moneybox->user_login ?></td>
						<td <?php 
						switch($moneybox->moneybox_id)
						{
							case 'Curious': $t=$c;break;
							case 'Optimal':$t=$o;break;
							case 'Greedy':$t=$g;break;
							default: $t = "not found";break;
						} 
					?>title="<?= $t ?>"><?= $moneybox->moneybox_id ?></td>
					<td><?= $moneybox->amount ?></td>
					<td><?= $moneybox->profit ?></td>
					<td><?= $moneybox->profit_week ?></td>
					<td><?= $moneybox->broken ?></td>
					<td><?= $moneybox->insurance ?></td>
					<td><?= $moneybox->date ?></td>
					<td><?= $moneybox->min_interval ?></td>
				</tr>
			<? } ?>
		</table>
	</div>