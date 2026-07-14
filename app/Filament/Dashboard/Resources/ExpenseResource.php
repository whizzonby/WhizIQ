<?php

namespace App\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use App\Models\TaxCategory;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Actions;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Expenses';

    protected static UnitEnum|string|null $navigationGroup = 'Money';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Section 1: Basic Expense Information
                Section::make('Expense Information')
                    ->description('Enter the basic details of your expense')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('date')
                                    ->label('Date')
                                    ->required()
                                    ->default(now())
                                    ->maxDate(now())
                                    ->native(false)
                                    ->displayFormat('M d, Y')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('amount')
                                    ->label('Amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->placeholder('0.00')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->maxLength(500)
                            ->placeholder('Describe what this expense was for...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                // Section 2: Tax Deduction Status (Visual & Interactive) - MOVED UP
                Section::make('Tax Deduction')
                    ->description(function ($get) {
                        $taxCategoryId = $get('tax_category_id');
                        if (!$taxCategoryId) {
                            return 'Select a category below to see tax deduction rules';
                        }

                        $taxCategory = TaxCategory::find($taxCategoryId);
                        if (!$taxCategory) {
                            return '';
                        }

                        return match ($taxCategory->deduction_behavior) {
                            'always' => '✅ This category is automatically tax-deductible in most countries',
                            'never' => '⛔ This category is NOT tax-deductible (personal/non-business expense)',
                            'requires_confirmation' => '⚠️ Please confirm if this expense was for business use',
                            default => '',
                        };
                    })
                    ->schema([
                        // Prominent visual status indicator
                        Forms\Components\Placeholder::make('tax_status_indicator')
                            ->label('Tax Status')
                            ->live()
                            ->content(function ($get) {
                                $taxCategoryId = $get('tax_category_id');
                                if (!$taxCategoryId) {
                                    return '⏳ Select a tax category below to see deduction status';
                                }

                                $taxCategory = TaxCategory::find($taxCategoryId);
                                if (!$taxCategory) {
                                    return '❓ Category not found';
                                }

                                $isDeductible = $get('is_tax_deductible') ?? false;

                                return match ($taxCategory->deduction_behavior) {
                                    'always' => '✅ ALWAYS TAX-DEDUCTIBLE (Auto-set)',
                                    'never' => '⛔ NEVER TAX-DEDUCTIBLE (Not allowed)',
                                    'requires_confirmation' => $isDeductible
                                        ? '✅ CONFIRMED AS TAX-DEDUCTIBLE'
                                        : '⚠️ AWAITING CONFIRMATION',
                                    default => '❓ Unknown status',
                                };
                            })
                            ->extraAttributes(function ($get) {
                                $taxCategoryId = $get('tax_category_id');
                                if (!$taxCategoryId) {
                                    return ['style' => 'font-size: 1.1rem; font-weight: 600; color: #6b7280; padding: 1rem; background: #f9fafb; border-radius: 0.5rem; text-align: center;'];
                                }

                                $taxCategory = TaxCategory::find($taxCategoryId);
                                if (!$taxCategory) {
                                    return ['style' => 'font-size: 1.1rem; font-weight: 600; padding: 1rem; border-radius: 0.5rem; text-align: center;'];
                                }

                                $isDeductible = $get('is_tax_deductible') ?? false;

                                $style = match ($taxCategory->deduction_behavior) {
                                    'always' => 'font-size: 1.1rem; font-weight: 600; color: #059669; background: #d1fae5; padding: 1rem; border-radius: 0.5rem; text-align: center; border: 2px solid #059669;',
                                    'never' => 'font-size: 1.1rem; font-weight: 600; color: #dc2626; background: #fee2e2; padding: 1rem; border-radius: 0.5rem; text-align: center; border: 2px solid #dc2626;',
                                    'requires_confirmation' => $isDeductible
                                        ? 'font-size: 1.1rem; font-weight: 600; color: #0284c7; background: #dbeafe; padding: 1rem; border-radius: 0.5rem; text-align: center; border: 2px solid #0284c7;'
                                        : 'font-size: 1.1rem; font-weight: 600; color: #d97706; background: #fef3c7; padding: 1rem; border-radius: 0.5rem; text-align: center; border: 2px solid #d97706;',
                                    default => 'font-size: 1.1rem; font-weight: 600; padding: 1rem; border-radius: 0.5rem; text-align: center;',
                                };

                                return ['style' => $style];
                            }),

                        // Confirmation question for "requires_confirmation" categories
                        Forms\Components\Placeholder::make('confirmation_question')
                            ->label('')
                            ->live()
                            ->content(function ($get) {
                                $taxCategoryId = $get('tax_category_id');
                                if (!$taxCategoryId) {
                                    return '';
                                }

                                $taxCategory = TaxCategory::find($taxCategoryId);
                                if (!$taxCategory || $taxCategory->deduction_behavior !== 'requires_confirmation') {
                                    return '';
                                }

                                return '❓ ' . ($taxCategory->confirmation_prompt ?? 'Is this expense for business use?');
                            })
                            ->visible(function ($get) {
                                $taxCategoryId = $get('tax_category_id');
                                if (!$taxCategoryId) {
                                    return false;
                                }

                                $taxCategory = TaxCategory::find($taxCategoryId);
                                return $taxCategory && $taxCategory->deduction_behavior === 'requires_confirmation';
                            })
                            ->extraAttributes(['style' => 'font-size: 1rem; font-weight: 500; color: #1f2937; padding: 0.75rem; background: #fef9c3; border-radius: 0.5rem; border-left: 4px solid #d97706;']),

                        // Toggle for "requires_confirmation" only
                        Forms\Components\Toggle::make('is_tax_deductible')
                            ->label('Yes, this expense is for business use')
                            ->inline(false)
                            ->visible(function ($get) {
                                $taxCategoryId = $get('tax_category_id');
                                if (!$taxCategoryId) {
                                    return false;
                                }

                                $taxCategory = TaxCategory::find($taxCategoryId);
                                return $taxCategory && $taxCategory->deduction_behavior === 'requires_confirmation';
                            })
                            ->disabled(function ($get) {
                                $taxCategoryId = $get('tax_category_id');
                                if (!$taxCategoryId) {
                                    return true;
                                }

                                $taxCategory = TaxCategory::find($taxCategoryId);
                                if (!$taxCategory) {
                                    return true;
                                }

                                return $taxCategory->deduction_behavior !== 'requires_confirmation';
                            })
                            ->live(),

                        // Deductible percentage info
                        Forms\Components\Placeholder::make('deduction_percentage_info')
                            ->label('Deduction Amount')
                            ->live()
                            ->content(function ($get) {
                                $taxCategoryId = $get('tax_category_id');
                                $isDeductible = $get('is_tax_deductible') ?? false;

                                if (!$taxCategoryId || !$isDeductible) {
                                    return '';
                                }

                                $taxCategory = TaxCategory::find($taxCategoryId);
                                if (!$taxCategory) {
                                    return '';
                                }

                                $percentage = $taxCategory->deduction_percentage;

                                if ($percentage == 100) {
                                    return '💰 100% of this expense is tax-deductible';
                                } else {
                                    return "💰 {$percentage}% of this expense is tax-deductible (typical for this category)";
                                }
                            })
                            ->visible(fn ($get) => $get('is_tax_deductible'))
                            ->extraAttributes(['style' => 'font-size: 0.95rem; color: #059669; padding: 0.5rem; background: #ecfdf5; border-radius: 0.5rem;']),

                        Forms\Components\TextInput::make('deductible_amount')
                            ->numeric()
                            ->prefix('$')
                            ->label('Custom Deductible Amount (Optional)')
                            ->helperText('Leave blank to use the category default percentage')
                            ->visible(fn ($get) => $get('is_tax_deductible'))
                            ->placeholder('Leave blank for automatic calculation'),

                        Forms\Components\Textarea::make('tax_notes')
                            ->rows(2)
                            ->label('Tax Notes (Optional)')
                            ->helperText('Add any additional notes for tax purposes')
                            ->visible(fn ($get) => $get('is_tax_deductible'))
                            ->placeholder('e.g., Client meeting notes, business trip details...'),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                // Section 3: Tax Category Selection (3-Tier System) - MOVED DOWN
                Section::make('Tax Category')
                    ->description('Select the tax category that best matches this expense')
                    ->schema([
                        Forms\Components\Select::make('tax_category_id')
                            ->relationship(
                                'taxCategory',
                                'name',
                                fn ($query) => $query
                                    ->where('is_active', true)
                                    ->whereNotNull('name')
                                    ->orderBy('sort_order')
                            )
                            ->getOptionLabelFromRecordUsing(fn (TaxCategory $record) => $record->name ?? 'Unknown')
                            ->searchable()
                            ->preload()
                            ->label('Category')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, $livewire) {
                                if ($state) {
                                    $taxCategory = TaxCategory::find($state);
                                    if ($taxCategory) {
                                        // Auto-set is_tax_deductible based on category behavior
                                        $defaultStatus = $taxCategory->getDefaultTaxDeductibleStatus();
                                        $set('is_tax_deductible', $defaultStatus);

                                        // Auto-populate category field with tax category name
                                        $set('category', $taxCategory->name);

                                        // Also set it directly on the data for saving
                                        $livewire->data['category'] = $taxCategory->name;
                                    }
                                }
                            })
                            ->helperText(function ($get) {
                                $taxCategoryId = $get('tax_category_id');
                                if (!$taxCategoryId) {
                                    return 'Choose the category that best describes this expense';
                                }

                                $taxCategory = TaxCategory::find($taxCategoryId);
                                if (!$taxCategory) {
                                    return '';
                                }

                                return $taxCategory->description ?? '';
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([])
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->default('—')
                    ->color(function (?string $state): string {
                        if (!$state) {
                            return 'gray';
                        }

                        return match (true) {
                            str_contains(strtolower($state), 'salary') || str_contains(strtolower($state), 'wage') => 'danger',
                            str_contains(strtolower($state), 'rent') || str_contains(strtolower($state), 'lease') => 'warning',
                            str_contains(strtolower($state), 'advertising') || str_contains(strtolower($state), 'marketing') => 'info',
                            str_contains(strtolower($state), 'software') || str_contains(strtolower($state), 'subscription') => 'success',
                            str_contains(strtolower($state), 'professional') || str_contains(strtolower($state), 'service') => 'primary',
                            str_contains(strtolower($state), 'travel') || str_contains(strtolower($state), 'transportation') => 'cyan',
                            str_contains(strtolower($state), 'meal') || str_contains(strtolower($state), 'entertainment') => 'purple',
                            str_contains(strtolower($state), 'utility') || str_contains(strtolower($state), 'utilities') => 'orange',
                            default => 'gray',
                        };
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('USD')
                            ->label('Total'),
                    ]),
                Tables\Columns\IconColumn::make('is_tax_deductible')
                    ->label('Tax Ded.')
                    ->boolean()
                    ->tooltip(fn ($record) => $record->is_tax_deductible
                        ? 'Deductible: $' . number_format($record->calculateDeductibleAmount(), 2)
                        : 'Not deductible'),
                Tables\Columns\TextColumn::make('description')
                    ->limit(40)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('taxCategory.name')
                    ->label('Tax Category')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(function () {
                        return Expense::where('user_id', auth()->id())
                            ->whereNotNull('category')
                            ->distinct()
                            ->pluck('category', 'category')
                            ->filter()
                            ->toArray();
                    })
                    ->searchable()
                    ->multiple()
                    ->label('Filter by Category'),
                Tables\Filters\TernaryFilter::make('is_tax_deductible')
                    ->label('Tax Deductible')
                    ->placeholder('All expenses')
                    ->trueLabel('Deductible only')
                    ->falseLabel('Non-deductible only'),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($query, $date) => $query->whereDate('date', '>=', $date))
                            ->when($data['until'], fn ($query, $date) => $query->whereDate('date', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'From ' . \Carbon\Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Until ' . \Carbon\Carbon::parse($data['until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canCreate(Expense::class, 'finance_expenses_limit') ?? false;
    }
}
