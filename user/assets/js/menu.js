
document.addEventListener('DOMContentLoaded', function() {
    console.log('menu.js - Navigation catégories uniquement');
    

    const categoryButtons = document.querySelectorAll('#category-nav button');
    const categorySections = document.querySelectorAll('.category-section');
    
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
        
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const category = this.dataset.category;
            
      
            categorySections.forEach(section => {
                section.style.display = (category === 'all' || section.id === category) 
                    ? 'block' 
                    : 'none';
            });
            
    
            if (category !== 'all') {
                const target = document.getElementById(category);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });
    
    console.log('Navigation catégories initialisée');
});