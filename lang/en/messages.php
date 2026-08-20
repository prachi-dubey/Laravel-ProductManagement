<?php

return [

    'auth' => [
        'registered' => 'Registered successfully.',
        'logged_in' => 'Logged in successfully.',
        'logged_out' => 'Logged out successfully.',
        'me_retrieved' => 'Authenticated user retrieved.',
        'credentials_invalid' => 'The provided credentials are incorrect.',
        'admin_required' => 'Admin access required.',
        'profile_updated' => 'Profile updated successfully.',
    ],

    'products' => [
        'listed' => 'Products retrieved successfully.',
        'created' => 'Product created successfully.',
        'shown' => 'Product retrieved successfully.',
        'updated' => 'Product updated successfully.',
        'deleted' => 'Product deleted successfully.',
        'categories_synced' => 'Product categories synced successfully.',
        'in_use' => 'Cannot delete product that appears on existing orders.',
        'not_found' => 'Product not found.',
    ],

    'categories' => [
        'listed' => 'Categories retrieved successfully.',
        'created' => 'Category created successfully.',
        'shown' => 'Category retrieved successfully.',
        'updated' => 'Category updated successfully.',
        'deleted' => 'Category deleted successfully.',
        'in_use' => 'Cannot delete category while products are still linked to it.',
        'not_found' => 'Category not found.',
    ],

    'orders' => [
        'listed' => 'Orders retrieved successfully.',
        'placed' => 'Order placed successfully.',
        'shown' => 'Order retrieved successfully.',
        'invalid_address' => 'No shipping address found on your profile.',
        'product_unavailable' => 'One or more products are unavailable.',
        'insufficient_stock' => 'Insufficient stock for :name (available: :available).',
        'not_found' => 'Order not found.',
    ],

    'errors' => [
        'validation' => 'The given data was invalid.',
        'unauthenticated' => 'Unauthenticated.',
        'forbidden' => 'This action is unauthorized.',
        'not_found' => 'Resource not found.',
        'user_not_found' => 'User not found.',
        'method_not_allowed' => 'The HTTP method is not allowed for this endpoint.',
        'syntax' => 'Something went wrong.',
        'server' => 'Server error.',
        'bad_request' => 'Bad request.',
    ],

];
