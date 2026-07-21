<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'board-index')->name('boards.index');
Route::livewire('/boards/{board}', 'board-show')->name('boards.show');
