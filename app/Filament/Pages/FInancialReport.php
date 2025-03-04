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
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Filament\Tables\Columns\Summarizers\Sum;
use App\Models\TransactionSummary;

class FinancialReport extends Page implements HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

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
    public function getHeading(): string
{
    return 'Financial Report - ' . auth()->user()->name;
}
    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return $this->getTransactionSummariesQuery();
            })
            ->columns([
                TextColumn::make('period')
                    ->label('Period')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('credit')
                    ->label('Credit')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('debit')
                    ->label('Debit')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('INR')
                    ->sortable()
                    ->state(function ($record) {
                        return $record['credit'] - $record['debit'];
                    })
                    ->color(function ($state) {
                        return $state > 0 ? 'success' : 'danger';
                    }),
            ])
            ->defaultSort('period', 'desc')
            ->striped()
            ->paginated(false)
            ->contentGrid([
                'md' => 1,
            ]);
    }

    public function getDailyTransactionsForCalendar()
    {
        $start = Carbon::parse($this->startDate)->startOfMonth();
        $end = Carbon::parse($this->endDate)->endOfMonth();

        $transactions = Transaction::where('user_id', auth()->id())
            ->whereBetween('date', [$start, $end])
            ->select(
                DB::raw('DATE(date) as date'),
                DB::raw('SUM(CASE WHEN type = "credit" THEN amount ELSE 0 END) as credit'),
                DB::raw('SUM(CASE WHEN type = "debit" THEN amount ELSE 0 END) as debit')
            )
            ->groupBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => [
                    'credit' => (float)$item->credit,
                    'debit' => (float)$item->debit,
                ]];
            })->toArray();

        return $transactions;
    }

    protected function getTransactionSummariesQuery(): Builder
    {
        $summaries = [
            [
                'id' => 1,
                'period' => 'Daily',
                'credit' => $this->getDailySummary()['credit'],
                'debit' => $this->getDailySummary()['debit'],
                'sort_order' => 1,
            ],
            [
                'id' => 2,
                'period' => 'Weekly',
                'credit' => $this->getWeeklySummary()['credit'],
                'debit' => $this->getWeeklySummary()['debit'],
                'sort_order' => 2,
            ],
            [
                'id' => 3,
                'period' => 'Monthly',
                'credit' => $this->getMonthlySummary()['credit'],
                'debit' => $this->getMonthlySummary()['debit'],
                'sort_order' => 3,
            ],
            [
                'id' => 4,
                'period' => 'Yearly',
                'credit' => $this->getYearlySummary()['credit'],
                'debit' => $this->getYearlySummary()['debit'],
                'sort_order' => 4,
            ],
        ];

        $query = DB::table(function ($query) use ($summaries) {
            $first = true;
            foreach ($summaries as $summary) {
                if ($first) {
                    $query->selectRaw("? as id, ? as period, ? as credit, ? as debit, ? as sort_order", [
                        $summary['id'],
                        $summary['period'],
                        $summary['credit'],
                        $summary['debit'],
                        $summary['sort_order'],
                    ]);
                    $first = false;
                } else {
                    $query->unionAll(
                        DB::query()->selectRaw("? as id, ? as period, ? as credit, ? as debit, ? as sort_order", [
                            $summary['id'],
                            $summary['period'],
                            $summary['credit'],
                            $summary['debit'],
                            $summary['sort_order'],
                        ])
                    );
                }
            }
        }, 'summaries');

        return Transaction::setQuery($query);
    }

    public function getTableRecordKey($record): string
    {
        return (string) $record->id;
    }

    private function getDailySummary()
    {
        $today = Carbon::today();

        return [
            'credit' => Transaction::where('user_id', auth()->id())
                ->where('type', 'credit')
                ->whereDate('date', $today)
                ->sum('amount'),
            'debit' => Transaction::where('user_id', auth()->id())
                ->where('type', 'debit')
                ->whereDate('date', $today)
                ->sum('amount'),
        ];
    }

    private function getWeeklySummary()
    {
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(6);

        return [
            'credit' => Transaction::where('user_id', auth()->id())
                ->where('type', 'credit')
                ->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate)
                ->sum('amount'),
            'debit' => Transaction::where('user_id', auth()->id())
                ->where('type', 'debit')
                ->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate)
                ->sum('amount'),
        ];
    }

    private function getMonthlySummary()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return [
            'credit' => Transaction::where('user_id', auth()->id())
                ->where('type', 'credit')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('amount'),
            'debit' => Transaction::where('user_id', auth()->id())
                ->where('type', 'debit')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('amount'),
        ];
    }

    private function getYearlySummary()
    {
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();

        return [
            'credit' => Transaction::where('user_id', auth()->id())
                ->where('type', 'credit')
                ->whereBetween('date', [$startOfYear, $endOfYear])
                ->sum('amount'),
            'debit' => Transaction::where('user_id', auth()->id())
                ->where('type', 'debit')
                ->whereBetween('date', [$startOfYear, $endOfYear])
                ->sum('amount'),
        ];
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

            $credits = Transaction::where('user_id', auth()->id())
                ->where('type', 'credit')
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->sum('amount');

            $debits = Transaction::where('user_id', auth()->id())
                ->where('type', 'debit')
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
        return Transaction::where('user_id', auth()->id())
            ->where('type', 'debit')
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
        return Transaction::where('user_id', auth()->id())
            ->where('type', 'debit')
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->category->name => $item->total]);
    }

    public function getExpenseByType()
    {
        return Transaction::where('user_id', auth()->id())
            ->where('type', 'debit')
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->select('expense_type', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_type')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->expense_type ?? 'N/A' => $item->total]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-arrow-down-on-square')
                ->color('success')
                ->action('exportPdf'),

            Action::make('export_csv')
                ->label('Export CSV')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->action('exportCsv'),
        ];
    }

    public function exportPdf()
    {
        try {
            $transactions = Transaction::where('user_id', auth()->id())
                ->whereBetween('date', [$this->startDate, $this->endDate])
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

    private function getTotalIncome()
    {
        return Transaction::where('user_id', auth()->id())
            ->where('type', 'credit')
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('amount');
    }

    private function getTotalExpenses()
    {
        return Transaction::where('user_id', auth()->id())
            ->where('type', 'debit')
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('amount');
    }

    private function getNetCashFlow()
    {
        return $this->getTotalIncome() - $this->getTotalExpenses();
    }
    private function getTodayTransactions()
{
    $today = Carbon::today();

    return Transaction::where('user_id', auth()->id())
        ->whereDate('date', $today)
        ->orderBy('date', 'desc')
        ->get()
        ->map(function ($transaction) {
            return [
                'category' => $transaction->category->name ?? 'N/A',
                'amount' => $transaction->amount,
                'type' => $transaction->type,
                'description' => $transaction->description ?? 'No description',
                'expense_type' => $transaction->expense_type ?? 'N/A',
            ];
        });
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
            'lastThreeExpenses' => $this->getLastThreeExpenses(),
            'calendarData' => $this->getDailyTransactionsForCalendar(),
            'todayTransactions' => $this->getTodayTransactions(),
        ];
    }
}
