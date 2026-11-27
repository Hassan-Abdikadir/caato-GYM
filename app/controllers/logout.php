<?php
Auth::logout();
flash('success', 'You have been signed out.');
redirect('login');
