<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Income Categories
        $income = Category::create(['name' => 'Income']);
        Category::create(['name' => 'Salary', 'parent_id' => $income->id]);
        Category::create(['name' => 'Freelance', 'parent_id' => $income->id]);
        Category::create(['name' => 'Investments', 'parent_id' => $income->id]);
        Category::create(['name' => 'Gifts', 'parent_id' => $income->id]);
        Category::create(['name' => 'Business Revenue', 'parent_id' => $income->id]);
        Category::create(['name' => 'Rental Income', 'parent_id' => $income->id]);
        Category::create(['name' => 'Interest/Dividends', 'parent_id' => $income->id]);
        Category::create(['name' => 'Other Income', 'parent_id' => $income->id]);

        // Expenses Categories
        $expenses = Category::create(['name' => 'Expenses']);

        // Family Expenses
        $family = Category::create(['name' => 'Family Expense', 'parent_id' => $expenses->id]);
        $groceries = Category::create(['name' => 'Groceries', 'parent_id' => $family->id]);
        Category::create(['name' => 'Milk', 'parent_id' => $groceries->id]);
        Category::create(['name' => 'Bakery', 'parent_id' => $groceries->id]);
        Category::create(['name' => 'Vegetables', 'parent_id' => $groceries->id]);
        Category::create(['name' => 'Chicken/Meat', 'parent_id' => $groceries->id]);
        Category::create(['name' => 'Fruits', 'parent_id' => $groceries->id]);
        Category::create(['name' => 'Snacks', 'parent_id' => $groceries->id]);
        Category::create(['name' => 'Household Supplies', 'parent_id' => $family->id]);
        Category::create(['name' => 'Utilities', 'parent_id' => $family->id]);
        Category::create(['name' => 'Education', 'parent_id' => $family->id]);
        Category::create(['name' => 'Healthcare', 'parent_id' => $family->id]);
        Category::create(['name' => 'Childcare', 'parent_id' => $family->id]);
        Category::create(['name' => 'Insurance', 'parent_id' => $family->id]);

        // Personal Expenses
        $personal = Category::create(['name' => 'Personal Expense', 'parent_id' => $expenses->id]);
        Category::create(['name' => 'Clothing', 'parent_id' => $personal->id]);
        Category::create(['name' => 'Electronics', 'parent_id' => $personal->id]);
        Category::create(['name' => 'Entertainment', 'parent_id' => $personal->id]);
        Category::create(['name' => 'Dining Out', 'parent_id' => $personal->id]);
        Category::create(['name' => 'Travel', 'parent_id' => $personal->id]);
        Category::create(['name' => 'Hobbies', 'parent_id' => $personal->id]);
        Category::create(['name' => 'Gifts', 'parent_id' => $personal->id]);
        Category::create(['name' => 'Subscriptions', 'parent_id' => $personal->id]);
        Category::create(['name' => 'Personal Care', 'parent_id' => $personal->id]);
        Category::create(['name' => 'Fitness', 'parent_id' => $personal->id]);
        Category::create(['name' => 'Transportation', 'parent_id' => $personal->id]);
        Category::create(['name' => 'Miscellaneous', 'parent_id' => $personal->id]);

        // Shop Expenses
        $shop = Category::create(['name' => 'Shop Expense', 'parent_id' => $expenses->id]);
        Category::create(['name' => 'Rent', 'parent_id' => $shop->id]);
        Category::create(['name' => 'Wage', 'parent_id' => $shop->id]);
        Category::create(['name' => 'Goods Purchase', 'parent_id' => $shop->id]);
        Category::create(['name' => 'Loading/Unloading', 'parent_id' => $shop->id]);
        Category::create(['name' => 'Utilities', 'parent_id' => $shop->id]);
        Category::create(['name' => 'Maintenance', 'parent_id' => $shop->id]);
        Category::create(['name' => 'Marketing', 'parent_id' => $shop->id]);
        Category::create(['name' => 'Equipment', 'parent_id' => $shop->id]);
        Category::create(['name' => 'Taxes', 'parent_id' => $shop->id]);
        Category::create(['name' => 'Shipping', 'parent_id' => $shop->id]);
    }
}
