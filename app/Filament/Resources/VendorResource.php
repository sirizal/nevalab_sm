<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Filament\Resources\VendorResource\RelationManagers;
use App\Models\PaymentTerm;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Vendor';

    protected static ?string $navigationGroup = 'Vendors';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Vendor')
                            ->required()
                            ->unique(Vendor::class, 'code', ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nama Vendor')
                            ->required()
                            ->unique(Vendor::class, 'name', ignoreRecord: true),
                        TextInput::make('pic_name')
                            ->label('Nama PIC'),
                        TextInput::make('pic_email')
                            ->label('Email PIC')
                            ->email(),
                        TextInput::make('pic_phone')
                            ->label('No Telp PIC'),
                        TextInput::make('address_1')
                            ->label('Alamat 1'),
                        TextInput::make('address_2')
                            ->label('Alamat 2'),
                        TextInput::make('address_3')
                            ->label('Alamat 3'),
                        TextInput::make('city')
                            ->label('Kota'),
                        TextInput::make('tax_no')
                            ->label('No NPWP'),
                        Select::make('payment_term_id')
                            ->label('Payment Term')
                            ->options(PaymentTerm::all()->pluck('code', 'id')->toArray())
                            ->required()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('pic_name')
                    ->label('Nama Pic'),
                TextColumn::make('pic_email')
                    ->label('Email PIC'),
                TextColumn::make('pic_phone')
                    ->label('Telepon PIC'),
                TextColumn::make('paymentTerm.code')
                    ->label('Payment term')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'view' => Pages\ViewVendor::route('/{record}'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
