<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income and Expense Statement</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            background-color: #fff;
            color: #000;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .container {
            margin: 0 auto;
            max-width: 900px;
            padding: 15px;
            background-color: #fff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .header h4 {
            margin: 2px 0;
            font-size: 12px;
        }

        .header h3 {
            margin: 10px 0;
            font-size: 13px;
            font-weight: bold;
        }

        .table-container {
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }

        th,
        td {
            padding: 3px 4px;
            text-align: left;
        }

        th {
            background-color: #f7f7f7;
            color: #000;
            font-weight: bold;
        }

        /* Set specific column widths */
        th:nth-child(1),
        td:nth-child(1) {
            width: 40%;
        }

        th:nth-child(2),
        td:nth-child(2) {
            width: 30%;
        }

        th:nth-child(3),
        td:nth-child(3) {
            width: 30%;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .total-row {
            font-weight: bold;
            background-color: #f7f7f7;
        }

        .net-profit-loss {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
            margin-top: 20px;
        }

        .right-align {
            text-align: right;
        }

        .center-align {
            text-align: left;
        }

        .supplier-indent {
            padding-left: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <h1>Pioneers Association of South Australia</h1>
            <h3>23 Leigh Street, Adelaide 5000</h3>
            <h3>Income and Expense Statement</h3>

            @if (request('start_date') && request('end_date'))
                <p>{{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}</p>
            @elseif(request('month') && request('year'))
                <p>Month: {{ date('F', mktime(0, 0, 0, request('month'), 1)) }} {{ request('year') }}</p>
            @elseif(request('year'))
                <p>Year: {{ request('year') }}</p>
            @endif
        </div>

        <!-- Income Section -->
        <h3>Income</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="left-align">Account</th>
                        <th class="left-align">Selected Period</th>
                        <th class="left-align">Year to Date</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalIncome = 0;
                    @endphp

                    @foreach ($reportData as $parentGlCode => $transactions)
                        @php
                            $parentTotalIncome = 0;
                        @endphp

                        <!-- Sum all income transactions for the account -->
                        @foreach ($transactions as $transaction)
                            @if ($transaction->transaction_type_id == 1)
                                @php
                                    $parentTotalIncome += $transaction->amount;
                                    $totalIncome += $transaction->amount;
                                @endphp
                            @endif
                        @endforeach

                        <!-- Display the Account Name (Parent GL Code) and Total Income -->
                        @if ($parentTotalIncome > 0)
                            <tr>
                                <td class="supplier-indent">{{ $parentGlCode }}</td>
                                <td>${{ number_format($parentTotalIncome, 2) }}</td>
                                <td></td>
                            </tr>
                        @endif
                    @endforeach

                    <!-- Grand Total Income -->
                    <tr class="total-row">
                        <td class="left-align"><strong>Total Income</strong></td>
                        <td class="left-align"><strong>${{ number_format($totalIncome, 2) }}</strong></td>
                        <td class="left-align"><strong>$0.00</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Expenditure Section -->
        <h3>Expenses</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="left-align">Account</th>
                        <th class="left-align">Selected Period</th>
                        <th class="left-align">Year to Date</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalExpense = 0;
                    @endphp

                    @foreach ($reportData as $parentGlCode => $transactions)
                        @php
                            $parentTotalExpense = 0;
                        @endphp

                        <!-- Sum all expense transactions for the account -->
                        @foreach ($transactions as $transaction)
                            @if ($transaction->transaction_type_id == 2)
                                @php
                                    $parentTotalExpense += $transaction->amount;
                                    $totalExpense += $transaction->amount;
                                @endphp
                            @endif
                        @endforeach

                        <!-- Display the Account Name (Parent GL Code) and Total Expense -->
                        @if ($parentTotalExpense > 0)
                            <tr>
                                <td class="supplier-indent">{{ $parentGlCode }}</td>
                                <td>${{ number_format($parentTotalExpense, 2) }}</td>
                                <td></td>
                            </tr>
                        @endif
                    @endforeach

                    <!-- Grand Total Expense -->
                    <tr class="total-row">
                        <td class="left-align"><strong>Total Expense</strong></td>
                        <td class="left-align"><strong>${{ number_format($totalExpense, 2) }}</strong></td>
                        <td class="left-align"><strong>$0.00</strong></td>
                    </tr>
                    <tr></tr>
                    <tr></tr>
                    <tr>@php
                        $netProfitLoss = $totalIncome - $totalExpense;
                    @endphp
                        <th class="left-align">Net Surplus/Loss</th>
                        <th class="left-align">${{ number_format($netProfitLoss, 2) }}</th>
                        <th class="left-align">$0.00</th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
