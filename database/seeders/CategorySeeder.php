<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Categorias de Despesas
        $expenseCategories = [
            'Alimentação' => ['Supermercado', 'Restaurante', 'Delivery', 'Padaria'],
            'Moradia' => ['Aluguel', 'Condomínio', 'Energia', 'Água', 'Internet', 'Gás'],
            'Transporte' => ['Combustível', 'Transporte público', 'Aplicativos (Uber/99)', 'Manutenção veículo', 'Estacionamento'],
            'Saúde' => ['Farmácia', 'Consultas', 'Plano de saúde', 'Academia'],
            'Lazer' => ['Streaming', 'Cinema/Teatro', 'Viagens', 'Hobbies'],
            'Educação' => ['Cursos', 'Livros', 'Material escolar'],
            'Vestuário' => ['Roupas', 'Calçados', 'Acessórios'],
            'Pets' => ['Alimentação', 'Veterinário', 'Produtos'],
            'Outros' => [],
        ];

        foreach ($expenseCategories as $categoryName => $subcategories) {
            $category = Category::create([
                'family_id' => null, // null = categoria padrão do sistema
                'name' => $categoryName,
                'type' => 'expense',
                'is_default' => true,
            ]);

            foreach ($subcategories as $subcategoryName) {
                Subcategory::create([
                    'category_id' => $category->id,
                    'name' => $subcategoryName,
                    'is_default' => true,
                ]);
            }
        }

        // Categorias de Receitas
        $incomeCategories = [
            'Salário' => [],
            'Freelance' => [],
            'Investimentos' => [],
            'Outros' => [],
        ];

        foreach ($incomeCategories as $categoryName => $subcategories) {
            $category = Category::create([
                'family_id' => null,
                'name' => $categoryName,
                'type' => 'income',
                'is_default' => true,
            ]);

            foreach ($subcategories as $subcategoryName) {
                Subcategory::create([
                    'category_id' => $category->id,
                    'name' => $subcategoryName,
                    'is_default' => true,
                ]);
            }
        }
    }
}