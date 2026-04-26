import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Component, OnInit, inject } from '@angular/core';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-root',
  imports: [CommonModule, FormsModule],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css',
})
export class AppComponent implements OnInit {
  private readonly http = inject(HttpClient);

  recipes: Recipe[] = [];
  isLoading = false;
  isSubmitting = false;
  errorMessage = '';
  successMessage = '';
  form = {
    title: '',
    description: '',
  };

  ngOnInit(): void {
    this.loadRecipes();
  }

  loadRecipes(): void {
    this.isLoading = true;
    this.errorMessage = '';

    this.http.get<RecipeResponse>('/api/recipes').subscribe({
      next: ({ items }) => {
        this.recipes = items;
        this.isLoading = false;
      },
      error: () => {
        this.errorMessage = 'Impossible de charger les recettes.';
        this.isLoading = false;
      },
    });
  }

  createRecipe(): void {
    this.isSubmitting = true;
    this.errorMessage = '';
    this.successMessage = '';

    this.http.post<Recipe>('/admin/recipes', this.form).subscribe({
      next: (recipe) => {
        this.recipes = [recipe, ...this.recipes];
        this.form = {
          title: '',
          description: '',
        };
        this.successMessage = 'Recette créée avec succès.';
        this.isSubmitting = false;
      },
      error: (error) => {
        this.errorMessage =
          error?.error?.message ?? 'Impossible de créer la recette.';
        this.isSubmitting = false;
      },
    });
  }
}

interface Recipe {
  id: number;
  title: string;
  description: string;
  created_at: string;
}

interface RecipeResponse {
  items: Recipe[];
}
