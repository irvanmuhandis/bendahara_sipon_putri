<?php

namespace App\Http\Controllers;

use App\Models\Pay;
use App\Models\Bill;
use App\Models\User;
use App\Models\Trans;
use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Debt;
use App\Models\Dispen;
use App\Models\Santri;
use App\Models\Wallet;
use Carbon\Carbon;
use DateTime;
use Illuminate\Pagination\LengthAwarePaginator;

use function Database\Seeders\wallet;

class MasterController extends Controller
{

    var $walletTable = "accGirl_wallets";
    var $billTable = "accGirl_bills";
    var $payTable = "accGirl_pays";



    public function index()
    {
        $fil = request('filter');
        $req = request('value');
        $mode = request('mode');
        $debit = request('debit');
        $searchQuery = request('query');
        if ($mode == Trans::class) {

            if ($fil == '') {
                $fil = 'id';
                $req = 'desc';
            } else {
                if ($req == 0) {
                    $req = 'asc';
                } else {
                    $req = 'desc';
                }
            }
            if ($debit == 1) {
                $data = Trans::where('desc', 'like', "%{$searchQuery}%")
                    ->where('debit', '>', 0)
                    ->with(['wallet', 'account', 'operator'])
                    ->orderBy($fil, $req)->paginate(25);
                return $data;
            } else {
                $data = Trans::where('desc', 'like', "%{$searchQuery}%")
                    ->where('credit', '>', 0)
                    ->with(['wallet', 'account', 'operator'])
                    ->orderBy($fil, $req)->paginate(25);
                return $data;
            }
        } else {

            if ($fil == '') {
                $fil = 'id';
                $req = 'desc';
            } else {
                if ($req == 0) {
                    $req = 'asc';
                } else {
                    $req = 'desc';
                }
            }
            if ($debit == 1) {
                $data = Pay::whereHas('santri', function ($query) use ($searchQuery) {
                    $query->where('fullname', 'like', "%{$searchQuery}%")
                        ->where('option', 2);
                })
                    ->whereHas('payable')
                    ->with(['wallet', 'payable.account', 'santri', 'operator'])
                    ->orderBy($fil, $req)->paginate(25);
                return $data;
            } else {
                $data = Debt::whereHas('santri', function ($query) use ($searchQuery) {
                    $query->where('fullname', 'like', "%{$searchQuery}%")
                        ->where('option', 2);
                })
                    ->with(['wallet', 'santri', 'operator'])
                    ->orderBy($fil, $req)->paginate(25);
                return $data;
            }
        }
    }

    public function circulation()
    {
        $start = '';
        $end = '';
        $fil = request('filter');
        $req = request('value');

        if ($fil == '') {
            $fil = 'id';
            $req = 'desc';
        } else {
            if ($req == 0) {
                $req = 'asc';
            } else {
                $req = 'desc';
            }
        }

        if (request('start') == '' || request('end') == '') {
            $start = Carbon::now()->subDays(30);
            $end = Carbon::now();
        } else {
            $start = Carbon::parse(request('start'));
            $start->setTimezone('UTC');
            $start->format('Y-m-d H:i:s');
            $start->setTime(0, 0, 0);
            $end = Carbon::parse(request('end'));
            $end->setTimezone('UTC');
            $end->format('Y-m-d H:i:s');
            $end->setTime(0, 0, 0);
        }
        return Wallet::select(['created_at', 'id', 'wallet_name', 'wallet_type', 'debit', 'credit'])
            ->selectRaw("(SELECT SUM(debit) - SUM(credit) FROM {$this->walletTable} AS w2 WHERE w2.id <= {$this->walletTable}.id AND w2.wallet_type = {$this->walletTable}.wallet_type) AS saldo")
            ->whereBetween('created_at', [$start, $end])
            ->orderBy($fil, $req)
            ->paginate(10);
    }

    public function show($id)
    {
        $mode = request('mode');
        if ($mode == Pay::class) {
            $ledger = Pay::with(['wallet', 'operator', 'payable.account', 'santri'])->findOrFail($id);
        } else {
            $ledger = Trans::with(['wallet', 'operator', 'account'])->findOrFail($id);
        }

        return $ledger;
    }


    public function billing()
    {
        $searchQuery = request('search');
        $account = json_decode(request('account'), true);


        $query = Santri::where('fullname', 'like', "%{$searchQuery}%")
            ->where('option', '2')
            ->with('bill.account')
            ->withCount(['bill as bill_count' => function ($bill) use ($account) {
                $bill
                    ->select(DB::raw('count(distinct(month))'))
                    ->whereBetween('month', [request('start'), request('end')])
                    ->whereIn('account_id', $account)
                    ->where('payment_status', '<', 3);
            }])
            ->with(['bill' => function ($query) use ($account) {
                $query->selectRaw('month,nis,sum(amount) as sum_amount,sum(remainder) as sum_remain,count(id) as count')
                    ->whereBetween('month', [request('start'), request('end')])
                    ->whereIn('account_id', $account)
                    ->where('payment_status', '<', 3)
                    ->groupBy('month')
                    ->groupBy('nis')
                    ->orderBy('month');
            }])
            ->withSum(['bill as sum_remain' => function ($bill) use ($account) {
                $bill
                    ->whereBetween('month', [request('start'), request('end')])
                    ->whereIn('account_id', $account)
                    ->where('payment_status', '<', 3);
            }], 'remainder')
            ->withSum(['bill as sum_amount' => function ($bill) use ($account) {
                $bill
                    ->whereBetween('month', [request('start'), request('end')])
                    ->whereIn('account_id', $account)
                    ->where('payment_status', '<', 3);
            }], 'amount')
            ->havingRaw('bill_count >= ?', [request('length')])
            ->get();


        $sum = $query->sum('sum_remain');
        return response()->json([
            'data' => $query,
            'sum' => $sum
        ]);
    }


    public function accountSum(Request $request)
    {
        DB::enableQueryLog();
        $queryParams = $request->query();
        $start = $queryParams['start'];
        $end = $queryParams['end'];

        if ($start == '') {
            $start = Carbon::now()->startOfMonth();
        }
        if ($end == '') {
            $end = Carbon::now()->lastOfMonth();
        }

        $dateStart = explode('-', $start);
        $dateEnd = explode('-', $end);

        $strt = Carbon::createFromDate($dateStart[0], $dateStart[1], 1);
        $ends = Carbon::createFromDate($dateEnd[0], $dateEnd[1], 1)->endOfMonth();


        $bill = Account::where('account_type', 2)
            ->whereHas('bill', function ($query) {
                $query->whereHas('santri', function ($queries) {
                    $queries->where('option', 2);
                });
            })
            ->withSum(['bill' => function ($query) use ($strt, $ends) {
                $query->whereBetween('month', [$strt->format('Y-m'), $ends->format('Y-m')]);
            }], 'remainder')
            ->withSum(['bill' => function ($query) use ($strt, $ends) {
                $query->whereBetween('month', [$strt->format('Y-m'), $ends->format('Y-m')]);
            }], 'amount')

            ->get();

        $debt = Account::where('account_type', 1)
            ->whereHas('debt', function ($query) {
                $query->whereHas('santri', function ($queries) {
                    $queries->where('option', 2);
                });
            })
            ->withSum(['debt' => function ($query) use ($strt, $ends) {
                $query->whereBetween('created_at', [$strt, $ends]);
            }], 'remainder')
            ->withSum(['debt' => function ($query) use ($strt, $ends) {
                $query->whereBetween('created_at', [$strt, $ends]);
            }], 'amount')

            ->get();

        $other = Account::where('account_type', '=', 3)
            ->whereHas('trans')
            ->withSum(['trans' => function ($query) use ($strt, $ends) {
                $query->whereBetween('created_at', [$strt, $ends]);
            }], 'debit')
            ->withSum(['trans' => function ($query) use ($strt, $ends) {
                $query->whereBetween('created_at', [$strt, $ends]);
            }], 'credit')
            ->get();
        $sum_bill_remain = $bill->sum('bill_sum_remainder');
        $sum_debt_remain = $debt->sum('debt_sum_remainder');
        $sum_bill_amount = $bill->sum('bill_sum_amount');
        $sum_debt_amount = $debt->sum('debt_sum_amount');
        $sum_other_cre = $other->sum('trans_sum_credit');
        $sum_other_deb = $other->sum('trans_sum_debit');
        return response()->json([
            'bill' => $bill,
            'debt' => $debt,
            'other' => $other,
            'income_bill' => ($sum_bill_amount - $sum_bill_remain),
            'expense_debt' => ($sum_debt_amount),
            'bill_potential' => $sum_bill_amount,
            'debt_potential' => $sum_debt_amount,
            'income_other' => $sum_other_deb,
            'income_debt' => ($sum_debt_amount - $sum_debt_remain),
            'expense_other' => $sum_other_cre
        ]);
    }

    public function walletSum()
    {
        $bill = Wallet::where('account_type', '=', 2)
            ->whereHas('bill', function ($query) {
                $query->whereHas('santri', function ($queries) {
                    $queries->where('option', 2);
                });
            })
            ->withSum('bill', 'amount')
            ->withSum('bill', 'remainder')
            ->get();

        $debt =    Account::where('account_type', '=', 1)
            ->whereHas('debt', function ($query) {
                $query->whereHas('santri', function ($queries) {
                    $queries->where('option', 2);
                });
            })
            ->withSum('debt', 'amount')
            ->withSum('debt', 'remainder')
            ->get();

        $other = Account::where('account_type', '=', 3)
            ->whereHas('trans')
            ->withSum('trans', 'debit')
            ->withSum('trans', 'credit')
            ->get();
        return response()->json([
            'bill' => $bill,
            'debt' => $debt,
            'other' => $other
        ]);
    }

    public function statistic()
    {
        $debt = Debt::where('payment_status', '<', 3)->whereHas('santri', function ($query) {
            $query->where('option', 2);
        })->count();
        $bill = Bill::where('payment_status', '<', 3)->whereHas('santri', function ($query) {
            $query->where('option', 2);
        })->count();
        $santri = Santri::where('status', 1)->where('option', 2)->count();
        $dispen = Dispen::where('status', '=', 1)->whereHas('santri', function ($query) {
            $query->where('option', 2);
        })->count();
        return response()->json([
            'debt' => $debt,
            'bill' => $bill,
            'santri' => $santri,
            'dispen' => $dispen
        ]);
    }


    public function inout(Request $request)
    {
        DB::enableQueryLog();
        $queryParams = $request->query();
        $start = $queryParams['start'];
        $end = $queryParams['end'];

        if ($start == '') {
            $start = Carbon::now()->startOfMonth();
        }
        if ($end == '') {
            $end = Carbon::now()->lastOfMonth();
        }

        $dateStart = explode('-', $start);
        $dateEnd = explode('-', $end);

        $strt = Carbon::createFromDate($dateStart[0], $dateStart[1], 1);
        $ends = Carbon::createFromDate($dateEnd[0], $dateEnd[1], 1)->endOfMonth();

        $paybill =  Pay::query()
            ->whereHas('santri', function ($query) {
                $query->where('option', 2);
            })
            ->where('payable_type', Bill::class)
            ->join("{$this->billTable}", "{$this->billTable}.id", '=', "{$this->payTable}.payable_id")
            ->whereBetween("{$this->billTable}.month", [$strt->format('Y-m'), $ends->format('Y-m')])
            ->select(
                DB::raw('sum(payment) as `sum`'),
                DB::raw("{$this->billTable}.month as date")
            )
            ->groupByRaw('date')
            ->orderBy('date')
            ->get();

        $paydebt =  Pay::query()
            ->whereHas('santri', function ($query) {
                $query->where('option', 2);
            })
            ->where('payable_type', Debt::class)
            ->select(
                DB::raw('sum(payment) as `sum`'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as date")
            )
            ->whereHas('payable', function ($query) use ($start, $strt, $ends, $end) {
                $query->whereBetween(DB::raw('created_at'), [$strt, $ends]);
            })
            ->groupByRaw('date')
            ->orderBy('date')
            ->get();

        $trans =  Trans::whereBetween('created_at', [$strt, $ends])
            ->select(
                DB::raw('sum(debit) as `sum_debit`'),
                DB::raw('sum(credit) as `sum_credit`'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as date")
            )
            ->groupByRaw('date')
            ->orderBy('date')
            ->get();

        $debt =  Debt::with('account')
            ->whereHas('santri', function ($query) {
                $query->where('option', 2);
            })
            ->whereBetween('created_at', [$strt, $ends])
            ->select(
                DB::raw('sum(amount) as `debt`'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as date")
            )
            ->groupByRaw('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'debt' => $debt,
            'trans' => $trans,
            'paybill' => $paybill,
            'paydebt' => $paydebt
        ]);
    }
}
