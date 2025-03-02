<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;

class FinancialReport extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Money Management';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.financial-report';
    protected static ?string $slug = 'financial-report';
    protected static ?string $title = 'Financial Report';
    protected static ?int $navigationSort = 1;

    public $startDate;
    public $endDate;

    public function mount(): void
    {
        $this->startDate = now()->subWeeks(4)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('startDate')
                    ->label('Start Date')
                    ->required()
                    ->maxDate(now())
                    ->default(now()->subWeeks(4))
                    ->live(),
                DatePicker::make('endDate')
                    ->label('End Date')
                    ->required()
                    ->maxDate(now())
                    ->default(now())
                    ->live(),
            ]);
    }

    public function getCashFlowData()
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $weeks = [];
        $current = $start->copy();

        while ($current <= $end) {
            $weekStart = $current->copy()->startOfWeek();
            $weekEnd = $current->copy()->endOfWeek();

            $credits = Transaction::where('type', 'credit')
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->sum('amount');

            $debits = Transaction::where('type', 'debit')
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->sum('amount');

            $weeks[] = [
                'week' => $weekStart->format('Y-W'),
                'credits' => $credits,
                'debits' => $debits,
                'net' => $credits - $debits,
            ];

            $current->addWeek();
        }

        return $weeks;
    }

    public function getLastThreeExpenses()
    {
        return Transaction::where('type', 'debit')
            ->with('category')
            ->latest('date')
            ->take(3)
            ->get()
            ->map(function ($transaction) {
                return [
                    'category' => $transaction->category->name ?? 'N/A',
                    'amount' => $transaction->amount,
                    'date' => Carbon::parse($transaction->date)->format('d M Y'),
                    'description' => $transaction->description ?? 'No description',
                    'expense_type' => $transaction->expense_type ?? 'N/A',
                ];
            });
    }

    public function getExpenseByCategory()
    {
        $expenses = Transaction::where('type', 'debit')
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->category->name => $item->total]);

        return $expenses->toArray();
    }



    public function getExpenseByType()
    {
        return Transaction::where('type', 'debit')
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->select('expense_type', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_type')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->expense_type ?? 'N/A' => $item->total]);
    }

    public function getTotalIncome()
    {
        return Transaction::where('type', 'credit')
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('amount');
    }

    public function getTotalExpenses()
    {
        return Transaction::where('type', 'debit')
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('amount');
    }

    public function getNetCashFlow()
    {
        return $this->getTotalIncome() - $this->getTotalExpenses();
    }

    public function getMonthlyComparison()
    {
        $start = Carbon::parse($this->startDate)->startOfMonth();
        $end = Carbon::parse($this->endDate)->endOfMonth();
        $months = [];

        while ($start <= $end) {
            $monthStart = $start->copy()->startOfMonth();
            $monthEnd = $start->copy()->endOfMonth();

            $income = Transaction::where('type', 'credit')
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');

            $expenses = Transaction::where('type', 'debit')
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');

            $months[] = [
                'month' => $start->format('M Y'),
                'income' => $income,
                'expenses' => $expenses,
                'net' => $income - $expenses,
            ];

            $start->addMonth();
        }

        return $months;
    }

    public function getTopExpenseCategories()
    {
        return Transaction::where('type', 'debit')
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category->name,
                    'amount' => $item->total,
                    'percentage' => ($item->total / $this->getTotalExpenses()) * 100,
                ];
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-arrow-down-on-square') // Alternative icon
                ->color('success')
                ->action('exportPdf'),

            Action::make('export_csv')
                ->label('Export CSV')
                ->icon('heroicon-o-document-arrow-down') // Alternative icon
                ->color('primary')
                ->action('exportCsv'),
        ];
    }


    public function exportPdf()
    {
        try {
            $transactions = Transaction::whereBetween('date', [$this->startDate, $this->endDate])
                ->with('category')
                ->orderBy('date', 'desc')
                ->get();

            $pdf = Pdf::loadView('pdf.transactions', [
                'transactions' => $transactions,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'totalIncome' => $this->getTotalIncome(),
                'totalExpenses' => $this->getTotalExpenses(),
                'netCashFlow' => $this->getNetCashFlow(),
                'expenseByCategory' => $this->getExpenseByCategory(),
            ]);

            return response()->streamDownload(
                fn () => print($pdf->output()),
                'financial-report.pdf'
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Export Failed')
                ->body('Failed to generate PDF report.')
                ->danger()
                ->send();

            throw new Halt();
        }
    }

    public function exportCsv()
    {
        try {
            $transactions = Transaction::whereBetween('date', [$this->startDate, $this->endDate])
                ->with('category')
                ->get();

            $csv = \League\Csv\Writer::createFromString('');

            $csv->insertOne([
                'Date',
                'Type',
                'Category',
                'Amount (₹)',
                'Description',
                'Expense Type'
            ]);

            foreach ($transactions as $transaction) {
                $csv->insertOne([
                    $transaction->date,
                    ucfirst($transaction->type),
                    $transaction->category->name,
                    number_format($transaction->amount, 2),
                    $transaction->description ?? 'N/A',
                    $transaction->expense_type ?? 'N/A',
                ]);
            }

            return response($csv->toString(), 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="financial-report.csv"',
            ]);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Export Failed')
                ->body('Failed to generate CSV report.')
                ->danger()
                ->send();

            throw new Halt();
        }
    }

    protected function getViewData(): array
    {
        return [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'cashFlowData' => $this->getCashFlowData(),
            'expenseByCategory' => $this->getExpenseByCategory(),
            'expenseByType' => $this->getExpenseByType(),
            'totalIncome' => $this->getTotalIncome(),
            'totalExpenses' => $this->getTotalExpenses(),
            'netCashFlow' => $this->getNetCashFlow(),
            'monthlyComparison' => $this->getMonthlyComparison(),
            'topExpenseCategories' => $this->getTopExpenseCategories(),
            'lastThreeExpenses' => $this->getLastThreeExpenses(),
        ];
    }
}
