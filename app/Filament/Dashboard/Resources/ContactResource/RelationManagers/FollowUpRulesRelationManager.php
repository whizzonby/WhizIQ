<?php

namespace App\Filament\Dashboard\Resources\ContactResource\RelationManagers;

use App\Models\AftercareSequence;
use App\Models\AftercareTemplate;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class FollowUpRulesRelationManager extends RelationManager
{
    protected static string $relationship = 'followUpRules';

    protected static ?string $title = 'Follow-up Rules';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Radio::make('rule_mode')
                ->label('Follow-up type')
                ->options([
                    'template' => 'Single message (template)',
                    'sequence' => 'Multi-step sequence',
                ])
                ->default('template')
                ->live()
                ->required(),

            Forms\Components\Select::make('aftercare_template_id')
                ->label('Template')
                ->options(fn () => AftercareTemplate::where('user_id', auth()->id())
                    ->where('is_active', true)
                    ->pluck('name', 'id')
                )
                ->searchable()
                ->visible(fn ($get) => $get('rule_mode') !== 'sequence')
                ->required(fn ($get) => $get('rule_mode') === 'template')
                ->helperText('This template overrides the appointment type template for this contact.'),

            Forms\Components\Select::make('aftercare_sequence_id')
                ->label('Sequence')
                ->options(fn () => AftercareSequence::where('user_id', auth()->id())
                    ->where('is_active', true)
                    ->pluck('name', 'id')
                )
                ->searchable()
                ->visible(fn ($get) => $get('rule_mode') === 'sequence')
                ->required(fn ($get) => $get('rule_mode') === 'sequence')
                ->helperText('This sequence overrides the appointment type sequence for this contact.'),

            Forms\Components\Select::make('preferred_channel')
                ->label('Preferred channel')
                ->options([
                    'whatsapp' => 'WhatsApp',
                    'sms'      => 'SMS',
                ])
                ->placeholder('Use default (from template/sequence)')
                ->helperText('Override the delivery channel for this contact only.'),

            Forms\Components\Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->inline(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('template.name')
                    ->label('Template')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('sequence.name')
                    ->label('Sequence')
                    ->placeholder('—'),

                Tables\Columns\BadgeColumn::make('preferred_channel')
                    ->label('Channel')
                    ->placeholder('Default')
                    ->colors([
                        'success' => 'whatsapp',
                        'info'    => 'sms',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        // Clear the field not in use
                        if (($data['rule_mode'] ?? 'template') === 'sequence') {
                            $data['aftercare_template_id'] = null;
                        } else {
                            $data['aftercare_sequence_id'] = null;
                        }
                        unset($data['rule_mode']);
                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (($data['rule_mode'] ?? 'template') === 'sequence') {
                            $data['aftercare_template_id'] = null;
                        } else {
                            $data['aftercare_sequence_id'] = null;
                        }
                        unset($data['rule_mode']);
                        return $data;
                    }),
                DeleteAction::make(),
            ]);
    }
}
