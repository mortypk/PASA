<?php

namespace App\Http\Controllers;

use App\Models\GlCodesParent;
use App\Models\Transaction;
use App\Models\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        return view('page.reports.index');
    }

    public function show($type, Request $request)
    {
        switch ($type) {
            case 'income-and-expenditure':
                return $this->incomeAndExpenditure($request);
            case 'bank-register':
                return $this->bankRegister($request);
            case 'accounts-list':
                return $this->accountsList($request);
            case 'bank-reconciliation':
                return $this->bankReconciliation($request);
            case 'balance-sheet':
                return $this->balanceSheet($request);
            case 'trail-balance':
                return $this->trialBalance($request);
            default:
                abort(404);
        }
    }

    private function getIncomeExpenditureData($incomeTypeId, $expenditureTypeId, $startDate, $endDate, $month, $year)
    {
        $query = Transaction::select(
            'gl_codes_parent.name as parent_gl_code_name',
            'suppliers.name as supplier_name',
            'customers.name as customer_name',
            'transactions.amount',
            'transactions.transaction_type_id',
            'transactions.member_id',
            'transactions.customer_id',
            DB::raw("IFNULL(CONCAT(additional_member_info.membership_number, ' - ', members.family_name, ' ' , members.given_name), CONCAT(members.family_name, ' ' , members.given_name)) as membership_and_name")
        )
            ->join('gl_codes_parent', 'transactions.gl_code_id', '=', 'gl_codes_parent.id')
            ->leftJoin('suppliers', 'transactions.supplier_id', '=', 'suppliers.id')
            ->leftJoin('customers', 'transactions.customer_id', '=', 'customers.id')
            ->leftJoin('members', 'transactions.member_id', '=', 'members.id')
            ->leftJoin('additional_member_info', 'members.id', '=', 'additional_member_info.member_id')
            ->groupBy('membership_and_name', 'gl_codes_parent.name', 'suppliers.name', 'customers.name', 'transactions.amount', 'transactions.transaction_type_id', 'transactions.member_id', 'transactions.customer_id')
            ->orderBy('gl_codes_parent.name', 'asc');

        // Apply date filters
        if ($startDate && $endDate) {
            $query->whereBetween('transactions.created_at', [$startDate, $endDate . ' 23:59:59']);
        } elseif ($month && $year) {
            $query->whereMonth('transactions.created_at', $month)
                ->whereYear('transactions.created_at', $year);
        } elseif ($year) {
            $query->whereYear('transactions.created_at', $year);
        }

        // Fetch the data
        $results = $query->get();

        // Group by parent GL code
        $groupedResults = $results->groupBy('parent_gl_code_name');

        return $groupedResults;
    }

    private function generatePDF($reportData, $viewPath, $fileName = 'report.pdf')
    {
        $pdf = Pdf::loadView($viewPath, compact('reportData'));
        return $pdf->stream($fileName);
    }

    private function incomeAndExpenditure(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $month = $request->input('month');
        $year = $request->input('year');

        $incomeTypeId = TransactionType::where('name', 'Income')->value('id');
        $expenditureTypeId = TransactionType::where('name', 'Expenditure')->value('id');

        $reportData = $this->getIncomeExpenditureData($incomeTypeId, $expenditureTypeId, $startDate, $endDate, $month, $year);

        return $this->generatePDF($reportData, 'page.reports.income-and-expenditure', 'income-and-expenditure.pdf');
    }

    private function bankRegister(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $month = $request->input('month');
        $year = $request->input('year');
        $accountId = $request->input('bank_account');

        if (empty($accountId)) {
            return back()->with('error', 'Please select a bank account.');
        }

        $reportData = $this->getBankRegisterData($startDate, $endDate, $month, $year, $accountId);

        // Generate and return the PDF
        return $this->generatePDF($reportData, 'page.reports.bank-register', 'bank-register.pdf');
    }

    private function getBankRegisterData($startDate, $endDate, $month, $year, $accountId)
    {
        // Fetch the bank account name
        $account = DB::table('gl_codes_parent')
            ->where('id', $accountId)
            ->first();

        if (!$account) {
            return back()->with('error', 'Invalid bank account selected.');
        }

        // Start with the basic query for fetching transactions for the selected account
        $query = Transaction::select(
            'transactions.id',
            'transactions.created_at',
            'transactions.description',
            'transactions.amount',
            'transactions.transaction_type_id',
            'transactions.gl_code_id',
            'transactions.member_id',
            'transactions.customer_id',
            'transactions.supplier_id'
        )
            ->with(['member', 'customer', 'supplier']) // Eager load relationships
            ->where('gl_code_id', $accountId) // Filter by the selected bank account
            ->orderBy('created_at', 'asc');  // Order by date

        // Apply date filters
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
        } elseif ($month && $year) {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } elseif ($year) {
            $query->whereYear('created_at', $year);
        }

        // Fetch the transactions data
        $transactions = $query->get();

        // Initialize totals and running balance
        $totalDeposits = 0;
        $totalWithdrawals = 0;
        $balance = 0;

        foreach ($transactions as $transaction) {
            if ($transaction->transaction_type_id == 1) { // Income (Deposit)
                $totalDeposits += $transaction->amount;
                $balance += $transaction->amount;
            } elseif ($transaction->transaction_type_id == 2) { // Expense (Withdrawal)
                $totalWithdrawals += $transaction->amount;
                $balance -= $transaction->amount;
            }

            // Determine who is involved in the transaction
            if ($transaction->transaction_type_id == 1 && $transaction->member_id) {
                $transaction->party = optional($transaction->member)->family_name . ' ' . optional($transaction->member)->given_name; // Show member name
            } elseif ($transaction->transaction_type_id == 1 && $transaction->customer_id) {
                $transaction->party = optional($transaction->customer)->name; // Show customer name
            } elseif ($transaction->transaction_type_id == 2 && $transaction->supplier_id) {
                $transaction->party = optional($transaction->supplier)->name; // Show supplier name
            } else {
                $transaction->party = 'N/A'; // Default if no relevant party is found
            }

            $transaction->balance = $balance; // Append running balance to each transaction
        }

        // Return the transactions along with the totals and account name
        return [
            'transactions' => $transactions,
            'totalDeposits' => $totalDeposits,
            'totalWithdrawals' => $totalWithdrawals,
            'account_name' => $account->name,
            'balance' => $balance
        ];
    }

    //public function getBankAccounts()
    //{
    // Fetch the list of bank accounts
    //    $bankAccounts = DB::table('accounts')->select('id', 'name')->get();

    // Return the data in JSON format
    //    return response()->json($bankAccounts);
    //}

    public function getBankAccounts()
    {
        // Fetch the list of bank accounts where is_bank_account is true (1)
        $bankAccounts = DB::table('gl_codes_parent')
            ->select('id', 'name')
            ->where('is_bank_account', 1) // Add where condition to filter bank accounts
            ->get();

        // Return the data in JSON format
        return response()->json($bankAccounts);
    }

    public function accountsList(Request $request)
    {
        // Fetch the accounts data
        $accounts = GlCodesParent::with('accountType')->get();

        // Pass the data to the generatePDF method
        return $this->generatePDF($accounts, 'page.reports.accounts-list', 'accounts-list.pdf');
    }

    private function bankReconciliation(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $month = $request->input('month');
        $year = $request->input('year');
        $accountId = $request->input('bank_account');

        //if (empty($accountId)) {
        //    return back()->with('error', 'Please select a bank account.');
        //}

        $reportData = $this->getbankReconciliationData($startDate, $endDate, $month, $year, $accountId);

        // Generate and return the PDF
        return $this->generatePDF($reportData, 'page.reports.bank-reconciliation', 'bank-reconciliation.pdf');
    }

    private function getbankReconciliationData($startDate, $endDate, $month, $year, $accountId)
    {
        // Fetch the bank account name
        $account = DB::table('gl_codes_parent')
            ->where('id', $accountId)
            ->first();

        if (!$account) {
            return back()->with('error', 'Invalid bank account selected.');
        }

        // Start with the basic query for fetching transactions for the selected account
        $query = Transaction::select(
            'transactions.id',
            'transactions.created_at',
            'transactions.description',
            'transactions.amount',
            'transactions.transaction_type_id',
            'transactions.gl_code_id',
            'transactions.member_id',
            'transactions.customer_id',
            'transactions.supplier_id'
        )
            ->with(['member', 'customer', 'supplier']) // Eager load relationships
            ->where('gl_code_id', $accountId) // Filter by the selected bank account
            ->orderBy('created_at', 'asc');  // Order by date

        // Apply date filters
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
        } elseif ($month && $year) {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } elseif ($year) {
            $query->whereYear('created_at', $year);
        }

        // Fetch the transactions data
        $transactions = $query->get();

        // Initialize totals and running balance
        $totalDeposits = 0;
        $totalWithdrawals = 0;
        $balance = 0;

        foreach ($transactions as $transaction) {
            if ($transaction->transaction_type_id == 1) { // Income (Deposit)
                $totalDeposits += $transaction->amount;
                $balance += $transaction->amount;
            } elseif ($transaction->transaction_type_id == 2) { // Expense (Withdrawal)
                $totalWithdrawals += $transaction->amount;
                $balance -= $transaction->amount;
            }

            // Determine who is involved in the transaction
            if ($transaction->transaction_type_id == 1 && $transaction->member_id) {
                $transaction->party = optional($transaction->member)->family_name . ' ' . optional($transaction->member)->given_name; // Show member name
            } elseif ($transaction->transaction_type_id == 1 && $transaction->customer_id) {
                $transaction->party = optional($transaction->customer)->name; // Show customer name
            } elseif ($transaction->transaction_type_id == 2 && $transaction->supplier_id) {
                $transaction->party = optional($transaction->supplier)->name; // Show supplier name
            } else {
                $transaction->party = 'N/A'; // Default if no relevant party is found
            }

            $transaction->balance = $balance; // Append running balance to each transaction
        }

        // Return the transactions along with the totals and account name
        return [
            'transactions' => $transactions,
            'totalDeposits' => $totalDeposits,
            'totalWithdrawals' => $totalWithdrawals,
            'account_name' => $account->name,
            'balance' => $balance
        ];
    }

    private function balanceSheet(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $month = $request->input('month');
        $year = $request->input('year');
        $accountId = $request->input('bank_account');

        $reportData = $this->getbalanceSheetData($startDate, $endDate, $month, $year, $accountId);

        // Generate and return the PDF
        return $this->generatePDF('', 'page.reports.balance-sheet', 'balance-sheet.pdf');
    }

    private function getbalanceSheetData($startDate, $endDate, $month, $year, $accountId) {}

    private function trialBalance(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $month = $request->input('month');
        $year = $request->input('year');
        $accountId = $request->input('bank_account');

        //if (empty($accountId)) {
        //    return back()->with('error', 'Please select a bank account.');
        //}

        $reportData = $this->gettrialBalanceData($startDate, $endDate, $month, $year, $accountId);

        // Generate and return the PDF
        return $this->generatePDF($reportData, 'page.reports.trial-balance', 'trial-balance.pdf');
    }
    private function gettrialBalanceData($startDate, $endDate, $month, $year, $accountId)
    {
        // Initialize query for retrieving the relevant accounts
        $accounts = DB::table('gl_codes_parent')
            ->select('gl_codes_parent.id', 'gl_codes_parent.name', 'gl_codes_parent.account_type_id', 'at.name as account_type')
            ->leftJoin('account_types as at', 'gl_codes_parent.account_type_id', '=', 'at.id')
            ->get();

        // Initialize transactions query
        $transactionsQuery = DB::table('transactions')
            ->select(
                'gl_code_id',
                DB::raw('SUM(CASE WHEN transaction_type_id = 1 THEN amount ELSE 0 END) as debits'),
                DB::raw('SUM(CASE WHEN transaction_type_id = 2 THEN amount ELSE 0 END) as credits')
            )
            ->whereIn('gl_code_id', $accounts->pluck('id'));

        // Apply date filters if provided
        if ($startDate && $endDate) {
            $transactionsQuery->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($month && $year) {
            $transactionsQuery->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month);
        }

        // If a specific bank account is provided, filter by account_id
        if ($accountId) {
            $transactionsQuery->where('account_id', $accountId);
        }

        $transactions = $transactionsQuery->groupBy('gl_code_id')->get();

        // Map transactions to accounts
        $trialBalanceData = $accounts->map(function ($account) use ($transactions) {
            $accountTransactions = $transactions->firstWhere('gl_code_id', $account->id);
            $debits = $accountTransactions ? $accountTransactions->debits : 0;
            $credits = $accountTransactions ? $accountTransactions->credits : 0;

            return [
                'account_name' => $account->name,
                'account_type' => $account->account_type,
                'debits' => $debits,
                'credits' => $credits,
            ];
        });

        // Calculate totals
        $totalDebits = $trialBalanceData->sum('debits');
        $totalCredits = $trialBalanceData->sum('credits');

        return [
            'trialBalanceData' => $trialBalanceData,
            'totalDebits' => $totalDebits,
            'totalCredits' => $totalCredits,
        ];
    }
}
