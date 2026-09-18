// Открытие/закрытие выпадающего меню
document.querySelector('.account-btn').addEventListener('click', function() {
    this.parentElement.classList.toggle('active');
});

// Закрытие меню при клике вне его
document.addEventListener('click', function(e) {
    if (!e.target.closest('.account-dropdown')) {
        document.querySelector('.account-dropdown').classList.remove('active');
    }
});

// Поиск по карточкам без перезагрузки страницы
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-box input');
    const sortSelect = document.querySelector('.sort-options select');
    const showPastCheckbox = document.querySelector('.show-past input');
    const marketCards = document.querySelectorAll('.market-card');
    
    // Функция фильтрации и сортировки
    function filterAndSortMarkets() {
        const searchValue = searchInput.value.trim().toUpperCase();
        const sortValue = sortSelect.value;
        const showPast = showPastCheckbox.checked;
        
        marketCards.forEach(card => {
            const name = card.querySelector('h3').textContent.toUpperCase();
            const dates = card.querySelector('.market-dates').textContent;
            const isPast = dates.includes('завершена'); // Предполагаем, что для прошлых ярмарок добавляется пометка
            
            // Применяем фильтры
            const matchesSearch = searchValue === '' || name.includes(searchValue);
            const matchesPastFilter = showPast || !isPast;
            
            if (matchesSearch && matchesPastFilter) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Слушатели событий
    searchInput.addEventListener('input', filterAndSortMarkets);
    sortSelect.addEventListener('change', function() {
        this.form.submit();
    });
    showPastCheckbox.addEventListener('change', filterAndSortMarkets);
});

// Для поиска товаров
    document.addEventListener('DOMContentLoaded', function() {
        // Поиск по карточкам товаров без перезагрузки страницы
        const searchInput = document.querySelector('.search-box input');
        const productCards = document.querySelectorAll('.product-card');
        
        // Функция фильтрации товаров
        function filterProducts() {
            const searchValue = searchInput.value.trim().toUpperCase();
            
            productCards.forEach(card => {
                const productName = card.querySelector('h3').textContent.toUpperCase();
                const sellerName = card.querySelector('.product-seller').textContent.toUpperCase();
                
                // Применяем фильтр
                const matchesSearch = searchValue === '' || 
                                    productName.includes(searchValue) || 
                                    sellerName.includes(searchValue);
                
                if (matchesSearch) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Слушатель события ввода
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterProducts, 300);
        });

        // Инициализация фильтра при загрузке (на случай если есть значение в поле поиска)
        filterProducts();
    });