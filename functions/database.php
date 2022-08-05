<?php

namespace Moneybox;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MoneyboxDatabase
{

    /*

    This class contains methods to use insert, update and delete requests
    Every public function should take already integrity-checked parameters

    --- USE ONLY THIS CLASS TO ACCESS DATABASE VIA INSERT\DELETE\UPDATE QUERIES !  ---

    */

    private readonly Object $wpdb;

    private readonly Object $lastWeek;

    private readonly Object $currentWeek;

    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
        $this->lastWeek = $wpdb->get_results("SELECT * FROM YOURTABLE WHERE id=(SELECT MAX(id) FROM YOURTABLE)")[0];
        $this->currentWeek = $wpdb->get_results("SELECT * FROM YOURTABLE WHERE id=(SELECT MAX(id)-1 FROM YOURTABLE)")[0];
        // Declare weeks info here to use it further
    }

    /* USER INTERACTION */

    public function moneyboxWithdraw($request, int $type=0) // Type 0 means moneybox, type 1 - referal income
    {
        switch($type){
            case 0:

            $bAbleToWithdraw = 
            $this->wpdb->query(
                $this->wpdb->prepare(
                    "UPDATE YOURTABLE 
                    SET broken = 1 
                    WHERE id = %d 
                    AND user_id = %d 
                    AND min_interval <= %s
                    AND broken = 0", 
                    $request['moneybox_id'], 
                    $request['user_id'],
                    date('Y-m-d')
                )
            );

            if($bAbleToWithdraw != 0){
                $request['total'] = 
                $this->wpdb->get_results(
                    $this->wpdb->prepare(
                        "SELECT IF(insurance = 0 OR profit_week >= 0, SUM(amount + profit_week), amount) 
                        AS total 
                        FROM YOURTABLE  
                        WHERE id = %d",
                        $request['moneybox_id']
                    )
                )[0]->total;
                $this->wpdb->insert('YOURTABLE', $request);
            }

            break;
            case 1:

            $this->wpdb->query(
                $this->wpdb->prepare(
                    "INSERT INTO YOURTABLE 
                    (user_id , moneybox_id , total, cardnumber) 
                    VALUES 
                    (%d, 0, %f, %s)",
                    $request['user_id'], 
                    $request['referal_bank'], 
                    $request['cardnumber']
                )
            );
            $this->wpdb->query(
                $this->wpdb->prepare(
                    "UPDATE YOURTABLE 
                    SET referal_bank = 0 
                    WHERE id = %d", 
                    $request['user_id']
                )
            );

            break;
        }
    }


    public function moneyboxInsurance($moneybox_id, $user_id, $date) // Make user moneybox insured
    {
        $this->wpdb->get_results(
            $this->wpdb->prepare(
                "UPDATE YOURTABLE 
                SET insurance = true 
                WHERE id = %d 
                AND  user_id = %d 
                AND  insurance = 0 
                AND  `date` >= %s",
                $moneybox_id, $user_id, $this->currentWeek->date
            )
        );
    }

    /* ADMIN INTERACTION */ 

    public function moneyboxResetProfit() // Admin action, makes database rollback to previous condition (before profit has been sent)
    {
        $startbank = $this->wpdb->get_results("SELECT startbank FROM YOURTABLE WHERE id = ".$this->currentWeek->id."")[0]->startbank;
        $profit = $this->wpdb->get_results("SELECT endbank FROM YOURTABLE WHERE id = ".$this->currentWeek->id."")[0]->endbank - $startbank;
        $insurance_id = $this->currentWeek->id - 1;
        $insurance = $this->wpdb->get_results("SELECT insurance FROM YOURTABLE WHERE id=" . $insurance_id. "")[0]->insurance;
        if ($insurance != null) {
            $this->wpdb->get_results(
                $this->wpdb->prepare(
                    "UPDATE YOURTABLE 
                    SET startbank = ROUND(%f,4), 
                    endbank = ROUND(%f,4),
                    percent = 0, 
                    insurance = %f
                    WHERE id = %d",
                    $startbank, $startbank, $insurance, $this->currentWeek->id
                )
            );

            $this->wpdb->get_results(
                $this->wpdb->prepare(
                    "UPDATE YOURTABLE 
                    SET startbank = ROUND(startbank - %f, 4), 
                    endbank = ROUND(endbank - %f, 4), 
                    insurance = %f 
                    WHERE id > %d", 
                    $profit, $profit, $insurance, $this->currentWeek->id
                )
            );
        }
        $this->wpdb->get_results("UPDATE YOURTABLE SET profit_week = profit");
        $this->wpdb->get_results("UPDATE YOURTABLE SET our = our_prev");
    }

    public function moneyboxUpdateProfit($profit) // Admin action, calculates moneyboxes profit and also affects week table 
    {

        if ($profit >= 0) {
            $percent = round(($profit / $this->currentWeek->startbank) * 100, 4); 
                //floor
        } else {
            $percent = round(($profit / $this->currentWeek->startbank) * 100, 4); 
                //ceil
        }

        $update = array(
            'endbank' => round($profit + $this->currentWeek->endbank, 4),
            'percent' => $this->currentWeek->percent + $percent
        );
        $where = array(
            'id' => $this->currentWeek->id
        );

        $this->wpdb->update('YOURTABLE', $update, $where);

        $this->wpdb->get_results(
            $this->wpdb->prepare(
                "UPDATE YOURTABLE 
                SET startbank = ROUND(startbank + %f, 4),
                endbank = ROUND(endbank + %f, 4) 
                WHERE id = %d",
                $profit, $profit, $this->currentWeek->id + 1
            )
        );
        $current_week = $this->currentWeek->date;
        if ($percent >= 0) {
            $this->wpdb->get_results(
                "UPDATE YOURTABLE 
                SET profit_week =
                IF (profit_week < 0,

                    IF(((profit + amount) * $percent) / IF(insurance = 0, 100, 200) > profit_week *-1,

                        ROUND( ((profit + amount) * $percent / IF(insurance = 0, 100, 200) + profit_week) / IF(insurance = 0, 2, 4), 4),

                        ROUND(profit_week + (profit + amount) * $percent / IF(insurance = 0, 100, 200), 4)),

                    ROUND(profit_week + (profit + amount) * $percent / IF(insurance = 0, 200, 400), 4)
                    )
                WHERE `date` < '" . $current_week . "' 
                AND broken = 0");

            $insurance = $this->wpdb->get_results("SELECT profit, profit_week FROM YOURTABLE WHERE insurance = 1", ARRAY_A);

            $ins_sum = 0;
            foreach ($insurance as $value) {
                if($value['profit'] < 0 && $value['profit_week'] > 0){
                    $ins_sum += $value['profit_week'] + ABS($value['profit']);
                }else{
                    $ins_sum += $value['profit_week'] - $value['profit'];
                }
            }

            $this->wpdb->get_results(
                $this->wpdb->prepare(
                    "UPDATE YOURTABLE 
                    SET insurance = ROUND(insurance + %f, 4)
                    WHERE id >= %d",
                    $ins_sum, 
                    $this->currentWeek->id
                )
            ); 

            $our = $this->wpdb->get_results("SELECT profit, profit_week FROM YOURTABLE WHERE profit_week > 0", ARRAY_A);
            $our_sum = 0;
            foreach ($our as $value) {
                if($value['profit'] < 0){
                    $our_sum += $value['profit_week'];
                }else{
                    $our_sum += $value['profit_week'] - $value['profit']; // ABS?
                }
            }

            $this->wpdb->get_results(
                "UPDATE YOURTABLE 
                SET our  = (ROUND(our + $our_sum/100*20,4))");
        } else {
            $this->wpdb->get_results(
                $this->wpdb->prepare(
                    "UPDATE YOURTABLE 
                    SET profit_week = ROUND(profit_week + (profit + amount) * %f / 100, 4) 
                    WHERE `date` < %s AND broken = 0",
                    $percent,
                    $this->currentWeek->date
                )
            );
        }
    }

    public function moneyboxSupport($id, $type) // Admin action, updates user's referal bank according to some value from support funds table
    {
        $type = esc_sql($type);
        $amount = $this->wpdb->get_results("SELECT $type FROM YOURTABLE")[0]->$type;
        $this->wpdb->get_results("UPDATE YOURTABLE SET referal_bank = referal_bank + $amount WHERE id = $id");
        $this->wpdb->get_results("UPDATE YOURTABLE SET $type = 0");
    }

    public function moneyboxOurProfit() // Admin action, updates database after our clear profit withdrawal
    {
        $amount = $this->wpdb->get_results("SELECT our FROM YOURTABLE", ARRAY_A)[0]['our'];
        $this->wpdb->query("UPDATE YOURTABLE SET our = 0");
        $this->wpdb->query(
            $this->wpdb->prepare(
                "UPDATE YOURTABLE 
                SET endbank = endbank - $amount 
                WHERE id = %d", 
                $this->currentWeek->id
            )
        );
        $this->wpdb->query(
            $this->wpdb->prepare(
                "UPDATE YOURTABLE
                SET 
                startbank = startbank - %f, 
                endbank = endbank - %f 
                WHERE id > %d",
                $amount, 
                $amount,
                $this->currentWeek->id
            )
        );
    }

    public function moneyboxProcessRequest($number, $broken) // Admin action to withdraw request
    {
        if ($broken == 0) {
            $this->wpdb->get_results(
                $this->wpdb->prepare(
                    "DELETE FROM YOURTABLE 
                    WHERE moneybox_id = %d", 
                    $number
                )
            );
            /*  $this->wpdb->delete('YOURTABLE', $number);*/
            $this->wpdb->update('YOURTABLE', $broken, $number);
            $this->wpdb->get_results(
                $this->wpdb->prepare(
                    "UPDATE YOURTABLE
                    SET
                    broken = 0
                    WHERE id = %d", 
                    $number
                )
            );
        } else {
            if ($number != 0) {
                extract(
                    $this->wpdb->get_results(
                        $this->wpdb->prepare(
                            "SELECT user_id, 
                            amount,
                            profit_week, 
                            insurance 
                            FROM YOURTABLE 
                            WHERE id = %d",
                            $number 
                        ), ARRAY_A
                    )[0]
                ); // extract() converts large array into  $profit_week, $user_id, etc...
                if ($profit_week > 0) {
                    $this->wpdb->get_results(
                        $this->wpdb->prepare(
                            "UPDATE YOURTABLE
                            SET 
                            jackpot = jackpot + %f,
                            bonus = bonus + %f,
                            support = support + %f",
                            round($profit_week * 0.02),
                            round($profit_week * 0.08),
                            round($profit_week * 0.05)
                        )
                    );

                    $percents = array(0 => 0.1, 1 => 0.05, 2 => 0.05); // Counting referal
                    $child = $user_id;
                    for ($i = 0; $i < 3; $i++) {
                        $parent = $this->wpdb->get_results(
                            $this->wpdb->prepare(
                                "SELECT parent 
                                FROM YOURTABLE 
                                WHERE child = %d", $child
                            ), ARRAY_A
                        );
                        if (empty($parent)) break;
                        $this->wpdb->get_results(
                            $this->wpdb->prepare(
                                "UPDATE YOURTABLE 
                                SET referal_bank = referal_bank + %f
                                WHERE ID = %d",
                                round($profit_week * $percents[$i], 4), 
                                $parent[0]['parent']
                            )
                        );
                        $child = $parent[0]['parent'];
                    }
                }

                $moneybox_money = $profit_week + $amount;
                if ($moneybox_money < $amount && $insurance == 1) {
                    $moneybox_money = $amount;
                    $this->wpdb->get_results(
                        $this->wpdb->prepare(
                            "UPDATE YOURTABLE 
                            SET insurance = insurance + %f
                            WHERE id = %d", 
                            $profit_week, 
                            $this->lastWeek->id
                        )
                    );
                }


                $this->wpdb->get_results(
                    $this->wpdb->prepare(
                        "UPDATE YOURTABLE 
                        SET 
                        startbank = ROUND(startbank - %f, 4), 
                        endbank = ROUND(endbank - %f, 4)
                        WHERE id = %d",
                        $moneybox_money, 
                        $moneybox_money,
                        $this->lastWeek->id
                    )
                );
                $where = array('moneybox_id' => $number);
                $this->wpdb->delete('YOURTABLE', $where);
                $where = array('id' => $number);
                $this->wpdb->delete('YOURTABLE', $where);
            } else {
                $this->wpdb->query(
                    $this->wpdb->prepare(
                        "DELETE FROM YOURTABLE 
                        WHERE user_id = %d 
                        AND moneybox_id = 0", 
                        get_current_user_id()
                    )
                );
            }
        }
    }

    /* SDK & OUTLYING INTERACTION */

    public function qiwiInsertNewPayment($billId) // Autocalled function, used for avoid duplicated payments
    {
        $this->wpdb->get_results($this->wpdb->prepare("INSERT INTO YOURTABLE (billId) VALUES (%s)", $billId));
    }

    public function moneyboxAddAfterCheck($user_id, $order_id, $payment_details, $interval) // Autocalled function, used after a new moneybox was bought
    {
        $this->wpdb->get_results(
            $this->wpdb->prepare(
                "INSERT INTO YOURTABLE
                (user_id, moneybox_id, amount, profit, profit_week, broken, insurance, `date`, min_interval) 
                VALUES  
                (%d, %d, %f, 0, 0, false, false, %s, %s)",
                str_replace('user_', '', $user_id),
                str_replace('product_', '', $order_id),
                round($payment_details['amountusd'], 2),
                $payment_details['paymentDate'],
                $interval
            )
        );

        $this->wpdb->get_results(
            $this->wpdb->prepare(
                "UPDATE YOURTABLE 
                SET 
                startbank = ROUND(startbank + %f, 4), 
                endbank = ROUND(endbank + %f, 4) 
                WHERE id = %d",
                $payment_details['amountusd'],
                $payment_details['amountusd'],
                $this->lastWeek->id
            )
        );
    }
}
