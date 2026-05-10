<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\CompanyProfile;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class CompanyProfilePage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office';

    protected string $view = 'filament.dashboard.pages.company-profile-page';

    protected static ?string $navigationLabel = 'Company Profile';

    protected static ?string $title = 'Company Profile';

    protected static UnitEnum|string|null $navigationGroup = 'Money';

    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    public function mount(): void
    {
        $profile = auth()->user()->companyProfile;

        $this->form->fill([
            'company_name'  => $profile?->company_name,
            'tagline'       => $profile?->tagline,
            'address_line1' => $profile?->address_line1,
            'address_line2' => $profile?->address_line2,
            'city'          => $profile?->city,
            'state'         => $profile?->state,
            'zip'           => $profile?->zip,
            'country'       => $profile?->country,
            'phone'         => $profile?->phone,
            'email'         => $profile?->email,
            'website'       => $profile?->website,
            'logo_path'     => $profile?->logo_path ? [$profile->logo_path] : null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Business Identity')
                    ->icon('heroicon-o-building-office-2')
                    ->description('This information appears on your invoices, quotes, and receipts.')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('company_name')
                                ->label('Company / Business Name')
                                ->placeholder('e.g. Acme Consulting Ltd.')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('tagline')
                                ->label('Tagline / Slogan (optional)')
                                ->placeholder('e.g. Powering your success')
                                ->maxLength(255),
                        ]),

                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Company Logo')
                            ->image()
                            ->disk('public')
                            ->directory('company-logos')
                            ->imageResizeMode('contain')
                            ->imageCropAspectRatio('4:1')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('200')
                            ->maxSize(2048)
                            ->helperText('PNG or JPG, max 2 MB. Recommended: wide/horizontal logo (e.g. 400×100 px).')
                            ->columnSpanFull(),
                    ]),

                Section::make('Address')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\TextInput::make('address_line1')
                            ->label('Address Line 1')
                            ->placeholder('Street address')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('address_line2')
                            ->label('Address Line 2 (optional)')
                            ->placeholder('Suite, unit, etc.')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Grid::make(3)->schema([
                            Forms\Components\TextInput::make('city')
                                ->label('City')
                                ->maxLength(100),

                            Forms\Components\TextInput::make('state')
                                ->label('State / Region')
                                ->maxLength(100),

                            Forms\Components\TextInput::make('zip')
                                ->label('ZIP / Postal Code')
                                ->maxLength(20),
                        ]),

                        Forms\Components\TextInput::make('country')
                            ->label('Country')
                            ->maxLength(100),
                    ]),

                Section::make('Contact Details')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Grid::make(3)->schema([
                            Forms\Components\TextInput::make('phone')
                                ->label('Phone')
                                ->tel()
                                ->placeholder('+1 555 000 0000')
                                ->maxLength(50),

                            Forms\Components\TextInput::make('email')
                                ->label('Business Email')
                                ->email()
                                ->placeholder('billing@yourcompany.com')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('website')
                                ->label('Website')
                                ->url()
                                ->placeholder('https://yourcompany.com')
                                ->maxLength(255),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $logoPath = null;
        if (!empty($data['logo_path']) && is_array($data['logo_path'])) {
            $logoPath = reset($data['logo_path']);
        }

        $payload = [
            'company_name'  => $data['company_name'] ?? null,
            'tagline'       => $data['tagline'] ?? null,
            'address_line1' => $data['address_line1'] ?? null,
            'address_line2' => $data['address_line2'] ?? null,
            'city'          => $data['city'] ?? null,
            'state'         => $data['state'] ?? null,
            'zip'           => $data['zip'] ?? null,
            'country'       => $data['country'] ?? null,
            'phone'         => $data['phone'] ?? null,
            'email'         => $data['email'] ?? null,
            'website'       => $data['website'] ?? null,
            'logo_path'     => $logoPath,
        ];

        CompanyProfile::updateOrCreate(
            ['user_id' => auth()->id()],
            $payload
        );

        Notification::make()
            ->title('Company profile saved')
            ->success()
            ->body('Your company information will now appear on all documents.')
            ->send();
    }
}
