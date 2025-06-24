// =====================================
// SUPER LOCATION AUTOCOMPLETE SYSTEM
// Sistema súper completo que trae TODO: hoteles, lugares, ciudades, etc.
// =====================================

class SuperLocationAutocomplete {
    constructor() {
        this.searchTimeout = null;
        this.currentSuggestionsList = null;
        this.cache = new Map();
        this.isLoading = false;
        this.highlightedIndex = -1;
        this.currentField = null;
        this.currentType = null;
        
        // APIs que vamos a usar (todas gratuitas)
        this.apis = {
            nominatim: 'https://nominatim.openstreetmap.org/search',
            overpass: 'https://overpass-api.de/api/interpreter',
            photon: 'https://photon.komoot.io/api',
            geonames: 'https://secure.geonames.org/searchJSON'
        };
        
        // Usuario para GeoNames (registrarse gratis en geonames.org)
        this.geonamesUser = 'demo'; // ¡CAMBIAR por tu usuario!
    }

    // Inicializar el sistema
    initialize() {
        console.log('🚀 Inicializando SUPER autocompletado de ubicaciones...');
        this.addAdvancedCSS();
        this.setupAllFields();
        this.preloadPopularLocations();
    }

    // Configurar todos los campos existentes y futuros
    setupAllFields() {
        // Observador para detectar nuevos campos dinámicamente
        this.observeNewFields();
        
        // Configurar campos existentes
        this.setupLocationField('ubicacion');
        this.setupLocationField('lugar_salida', 'salida');
        this.setupLocationField('lugar_llegada', 'llegada');
    }

    // Observar nuevos campos que se agreguen dinámicamente
    observeNewFields() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        // Buscar campos de ubicación en el nuevo contenido
                        const locationFields = node.querySelectorAll ? 
                            node.querySelectorAll('#ubicacion, #lugar_salida, #lugar_llegada') : [];
                        
                        locationFields.forEach(field => {
                            const type = field.id === 'lugar_salida' ? 'salida' : 
                                        field.id === 'lugar_llegada' ? 'llegada' : 'general';
                            this.setupLocationField(field.id, type);
                        });
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Configurar un campo específico
    setupLocationField(fieldId, type = 'general') {
        const field = document.getElementById(fieldId);
        if (!field || field.dataset.autocompleteSetup) return;

        console.log(`✅ Configurando campo súper completo: ${fieldId}`);

        // Marcar como configurado
        field.dataset.autocompleteSetup = 'true';

        // Configurar contenedor
        this.setupFieldContainer(field);
        
        // Actualizar placeholder
        this.setAdvancedPlaceholder(field, type);
        
        // Event listeners avanzados
        field.addEventListener('input', (e) => this.handleAdvancedInput(e, type));
        field.addEventListener('focus', (e) => this.handleAdvancedFocus(e, type));
        field.addEventListener('blur', (e) => this.handleAdvancedBlur(e));
        field.addEventListener('keydown', (e) => this.handleAdvancedKeydown(e, type));
        
        // Agregar icono de búsqueda avanzado
        this.addAdvancedSearchIcon(field);
    }

    // Configurar contenedor del campo
    setupFieldContainer(field) {
        const container = field.parentElement;
        if (container.style.position !== 'relative') {
            container.style.position = 'relative';
        }
        
        // Mejorar estilo del campo
        field.style.paddingRight = '45px'; // Espacio para iconos
        field.style.transition = 'border-color 0.3s ease, box-shadow 0.3s ease';
    }

    // Placeholder súper detallado
    setAdvancedPlaceholder(field, type) {
        const placeholders = {
            general: 'Busca ciudades, hoteles, restaurantes, atracciones... ej: Hotel Ritz París',
            salida: 'Aeropuertos, estaciones, ciudades de salida... ej: Aeropuerto Madrid Barajas',
            llegada: 'Aeropuertos, estaciones, ciudades de llegada... ej: Aeropuerto Roma Fiumicino'
        };
        
        field.placeholder = placeholders[type] || placeholders.general;
    }

    // Icono de búsqueda súper avanzado
    addAdvancedSearchIcon(field) {
        const container = field.parentElement;
        const existingIcon = container.querySelector('.super-search-icon');
        if (existingIcon) return;

        const iconContainer = document.createElement('div');
        iconContainer.className = 'super-search-icon';
        iconContainer.style.cssText = `
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 5px;
            pointer-events: none;
            z-index: 10;
        `;
        
        iconContainer.innerHTML = `
            <span style="font-size: 14px; color: #64748b;">🔍</span>
            <span style="font-size: 12px; color: #94a3b8;">🌍</span>
        `;
        
        container.appendChild(iconContainer);
    }

    // Manejar entrada de texto avanzada
    handleAdvancedInput(event, type) {
        const field = event.target;
        const query = field.value.trim();
        
        this.currentField = field;
        this.currentType = type;

        // Limpiar timeout anterior
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        // Si es muy corto, mostrar sugerencias populares
        if (query.length === 0) {
            this.removeSuggestions();
            return;
        }
        
        if (query.length === 1) {
            this.showPopularSuggestions(field, type);
            return;
        }

        if (query.length < 2) {
            this.removeSuggestions();
            return;
        }

        // Verificar cache
        const cacheKey = `${query.toLowerCase()}_${type}`;
        if (this.cache.has(cacheKey)) {
            const cachedResults = this.cache.get(cacheKey);
            this.showAdvancedSuggestions(cachedResults, field, type);
            return;
        }

        // Mostrar loading avanzado
        this.showAdvancedLoading(field);
        
        // Buscar con delay
        this.searchTimeout = setTimeout(() => {
            this.performSuperSearch(query, field, type);
        }, 250); // Más rápido para mejor UX
    }

    // Realizar súper búsqueda combinando múltiples APIs
    async performSuperSearch(query, field, type) {
        try {
            this.isLoading = true;
            
            console.log(`🔍 Búsqueda súper completa para: "${query}"`);
            
            // Ejecutar múltiples búsquedas en paralelo
            const searches = await Promise.allSettled([
                this.searchNominatim(query, type),
                this.searchPhoton(query, type),
                this.searchOverpass(query, type),
                this.searchGeoNames(query, type)
            ]);

            // Combinar todos los resultados
            let allResults = [];
            searches.forEach((result, index) => {
                if (result.status === 'fulfilled' && result.value) {
                    allResults = allResults.concat(result.value);
                }
            });

            // Procesar y filtrar resultados
            const processedResults = this.processAdvancedResults(allResults, query, type);
            
            // Guardar en cache
            const cacheKey = `${query.toLowerCase()}_${type}`;
            this.cache.set(cacheKey, processedResults);
            
            this.hideAdvancedLoading(field);
            this.showAdvancedSuggestions(processedResults, field, type);

        } catch (error) {
            console.warn('Error en súper búsqueda:', error);
            this.hideAdvancedLoading(field);
            this.showFallbackSuggestions(query, field, type);
        } finally {
            this.isLoading = false;
        }
    }

    // Búsqueda en Nominatim (OpenStreetMap) - Súper completa
    async searchNominatim(query, type) {
        try {
            const extraParams = type === 'salida' || type === 'llegada' ? 
                '&class=aeroway,railway,highway' : '';
            
            const url = `${this.apis.nominatim}?` +
                `format=json&q=${encodeURIComponent(query)}&limit=15&` +
                `addressdetails=1&extratags=1&namedetails=1&` +
                `accept-language=es,en&dedupe=1${extraParams}`;

            const response = await fetch(url);
            const data = await response.json();
            
            return data.map(item => ({
                name: this.extractBestName(item),
                fullName: item.display_name,
                lat: parseFloat(item.lat),
                lng: parseFloat(item.lon),
                type: this.classifyAdvanced(item),
                category: this.getCategory(item),
                importance: item.importance || 0.5,
                source: 'OpenStreetMap',
                details: this.extractDetails(item),
                rating: this.estimateRating(item)
            }));
        } catch (error) {
            console.warn('Nominatim search failed:', error);
            return [];
        }
    }

    // Búsqueda en Photon (Elasticsearch para OSM)
    async searchPhoton(query, type) {
        try {
            const url = `${this.apis.photon}?` +
                `q=${encodeURIComponent(query)}&limit=10&lang=es`;

            const response = await fetch(url);
            const data = await response.json();
            
            if (data.features) {
                return data.features.map(item => ({
                    name: item.properties.name || item.properties.osm_value,
                    fullName: this.buildFullName(item.properties),
                    lat: item.geometry.coordinates[1],
                    lng: item.geometry.coordinates[0],
                    type: this.classifyPhoton(item.properties),
                    category: item.properties.osm_key,
                    importance: 0.7,
                    source: 'Photon',
                    details: item.properties
                }));
            }
        } catch (error) {
            console.warn('Photon search failed:', error);
        }
        return [];
    }

    // Búsqueda en Overpass (para POIs específicos)
    async searchOverpass(query, type) {
        try {
            // Query Overpass para hoteles, restaurantes, atracciones
            const overpassQuery = `
                [out:json][timeout:5];
                (
                  nwr["name"~"${query}",i]["tourism"];
                  nwr["name"~"${query}",i]["amenity"~"restaurant|hotel|cafe"];
                  nwr["name"~"${query}",i]["leisure"];
                );
                out center meta;
            `;

            const response = await fetch(this.apis.overpass, {
                method: 'POST',
                body: overpassQuery
            });
            
            const data = await response.json();
            
            if (data.elements) {
                return data.elements.slice(0, 8).map(item => {
                    const lat = item.lat || (item.center && item.center.lat);
                    const lng = item.lon || (item.center && item.center.lon);
                    
                    return {
                        name: item.tags.name,
                        fullName: this.buildOverpassName(item.tags),
                        lat: lat,
                        lng: lng,
                        type: this.classifyOverpass(item.tags),
                        category: item.tags.tourism || item.tags.amenity || item.tags.leisure,
                        importance: 0.8,
                        source: 'Overpass',
                        details: item.tags,
                        rating: this.parseRating(item.tags)
                    };
                }).filter(item => item.lat && item.lng);
            }
        } catch (error) {
            console.warn('Overpass search failed:', error);
        }
        return [];
    }

    // Calcular distancia entre dos puntos geográficos
    calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Radio de la Tierra en km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLng/2) * Math.sin(dLng/2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c; // Distancia en kilómetros
    }


    // Búsqueda en GeoNames
    async searchGeoNames(query, type) {
        try {
            const url = `${this.apis.geonames}?` +
                `q=${encodeURIComponent(query)}&maxRows=10&` +
                `username=${this.geonamesUser}&style=FULL&lang=es`;

            const response = await fetch(url);
            const data = await response.json();
            
            if (data.geonames) {
                return data.geonames.map(item => ({
                    name: item.name,
                    fullName: this.buildGeoNamesFullName(item),
                    lat: parseFloat(item.lat),
                    lng: parseFloat(item.lng),
                    type: this.classifyGeoNames(item),
                    category: item.fclName,
                    importance: 0.6,
                    source: 'GeoNames',
                    details: {
                        population: item.population,
                        elevation: item.elevation,
                        timezone: item.timezone
                    },
                    population: item.population || 0
                }));
            }
        } catch (error) {
            console.warn('GeoNames search failed:', error);
        }
        return [];
    }

    // Procesar y filtrar resultados avanzados
    processAdvancedResults(results, query, type) {
        if (!results || results.length === 0) return [];

        // Remover duplicados inteligentemente
        const uniqueResults = this.removeDuplicatesAdvanced(results);
        
        // Filtrar por tipo si es necesario
        const filteredResults = this.filterByType(uniqueResults, type);
        
        // Ordenar por relevancia avanzada
        const sortedResults = this.sortByAdvancedRelevance(filteredResults, query);
        
        // Limitar resultados
        return sortedResults.slice(0, 12);
    }

    // Remover duplicados de forma inteligente
    removeDuplicatesAdvanced(results) {
        const seen = new Map();
        const threshold = 0.001; // ~100 metros
        
        return results.filter(item => {
            if (!item.lat || !item.lng || !item.name) return false;
            
            // Buscar duplicados cercanos
            for (let [key, existing] of seen) {
                const distance = this.calculateDistance(
                    item.lat, item.lng,
                    existing.lat, existing.lng
                );
                
                if (distance < threshold && this.namesAreSimilar(item.name, existing.name)) {
                    // Es duplicado, mantener el mejor
                    if (item.importance > existing.importance) {
                        seen.set(key, item);
                    }
                    return false;
                }
            }
            
            const key = `${item.lat.toFixed(3)}_${item.lng.toFixed(3)}_${item.name.substring(0, 10)}`;
            seen.set(key, item);
            return true;
        });
    }

   // Verificar si dos nombres son similares
namesAreSimilar(name1, name2) {
    // Función para normalizar nombres
    const normalize = (str) => {
        return str.toLowerCase()
                  .replace(/[^\w\s]/g, '') // Remover caracteres especiales
                  .replace(/\s+/g, ' ')    // Normalizar espacios
                  .trim();
    };
    
    const n1 = normalize(name1);
    const n2 = normalize(name2);
    
    // Coincidencia exacta
    if (n1 === n2) return true;
    
    // Uno contiene al otro
    if (n1.includes(n2) || n2.includes(n1)) return true;
    
    // Verificar distancia de Levenshtein para nombres muy similares
    return this.levenshteinDistance(n1, n2) <= 2;
} 

    // Filtrar por tipo de búsqueda
    filterByType(results, type) {
        if (type === 'salida' || type === 'llegada') {
            // Priorizar aeropuertos, estaciones, etc.
            return results.sort((a, b) => {
                const aIsTransport = ['airport', 'station', 'terminal', 'port'].includes(a.type);
                const bIsTransport = ['airport', 'station', 'terminal', 'port'].includes(b.type);
                
                if (aIsTransport && !bIsTransport) return -1;
                if (!aIsTransport && bIsTransport) return 1;
                return 0;
            });
        }
        
        return results;
    }

    // Ordenar por relevancia súper avanzada
    sortByAdvancedRelevance(results, query) {
        const queryLower = query.toLowerCase();
        
        return results.sort((a, b) => {
            // Factor 1: Coincidencia exacta al inicio
            const aStartsExact = a.name.toLowerCase().startsWith(queryLower) ? 2 : 0;
            const bStartsExact = b.name.toLowerCase().startsWith(queryLower) ? 2 : 0;
            
            // Factor 2: Contiene la búsqueda
            const aContains = a.name.toLowerCase().includes(queryLower) ? 1 : 0;
            const bContains = b.name.toLowerCase().includes(queryLower) ? 1 : 0;
            
            // Factor 3: Importancia/popularidad
            const aImportance = a.importance || 0;
            const bImportance = b.importance || 0;
            
            // Factor 4: Rating si existe
            const aRating = a.rating || 0;
            const bRating = b.rating || 0;
            
            // Factor 5: Población si existe
            const aPopulation = Math.log(a.population || 1);
            const bPopulation = Math.log(b.population || 1);
            
            // Calcular score total
            const aScore = aStartsExact + aContains + aImportance + (aRating * 0.1) + (aPopulation * 0.001);
            const bScore = bStartsExact + bContains + bImportance + (bRating * 0.1) + (bPopulation * 0.001);
            
            return bScore - aScore;
        });
    }

    // Mostrar sugerencias súper avanzadas
    showAdvancedSuggestions(suggestions, field, type) {
        this.removeSuggestions();
        
        if (!suggestions || suggestions.length === 0) {
            this.showNoResultsMessage(field);
            return;
        }

        this.currentSuggestionsList = document.createElement('div');
        this.currentSuggestionsList.className = 'super-location-suggestions';
        this.currentSuggestionsList.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 16px 16px;
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            animation: slideDown 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        `;

        // Header con contador de resultados
        const header = this.createResultsHeader(suggestions.length, field.value);
        this.currentSuggestionsList.appendChild(header);

        // Crear elementos de sugerencia avanzados
        suggestions.forEach((suggestion, index) => {
            const item = this.createAdvancedSuggestionItem(suggestion, index, field, type);
            this.currentSuggestionsList.appendChild(item);
        });

        // Footer avanzado
        const footer = this.createAdvancedFooter();
        this.currentSuggestionsList.appendChild(footer);

        field.parentElement.appendChild(this.currentSuggestionsList);
        this.highlightedIndex = -1;
    }

    // Crear header de resultados
    createResultsHeader(count, query) {
        const header = document.createElement('div');
        header.className = 'results-header';
        header.style.cssText = `
            padding: 12px 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        `;
        
        header.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>📍 ${count} resultados para "${query}"</span>
                <span style="font-size: 11px;">🌍 Búsqueda global</span>
            </div>
        `;
        
        return header;
    }
    // Función para resaltar texto que coincide con la búsqueda
highlightQuery(text, query) {
    if (!query || query.length < 2) return text;
    
    // Escapar caracteres especiales de regex
    const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`(${escapedQuery})`, 'gi');
    
    return text.replace(regex, '<strong style="color: var(--primary-color, #667eea); background: rgba(102, 126, 234, 0.1); padding: 0 2px; border-radius: 2px;">$1</strong>');
}


    // Crear elemento de sugerencia súper avanzado
    createAdvancedSuggestionItem(suggestion, index, field, type) {
        const item = document.createElement('div');
        item.className = 'super-suggestion-item';
        item.dataset.index = index;
        
        const icon = this.getAdvancedIcon(suggestion.type, suggestion.category);
        const badge = this.createTypeBadge(suggestion.type, suggestion.source);
        const rating = this.createRatingDisplay(suggestion.rating);
        const details = this.createDetailsDisplay(suggestion.details);
        
        item.style.cssText = `
            padding: 16px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.2s ease;
            font-size: 14px;
            position: relative;
        `;
        
        item.innerHTML = `
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <div style="font-size: 20px; margin-top: 2px;">${icon}</div>
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <div style="font-weight: 600; color: #1e293b; font-size: 15px;">
                            ${this.highlightQuery(suggestion.name, field.value)}
                        </div>
                        ${badge}
                        ${rating}
                    </div>
                    <div style="color: #64748b; font-size: 13px; line-height: 1.4; margin-bottom: 4px;">
                        ${suggestion.fullName}
                    </div>
                    ${details}
                </div>
                <div style="font-size: 11px; color: #94a3b8; text-align: right;">
                    ${suggestion.source}
                </div>
            </div>
        `;

        // Event listeners avanzados
        item.addEventListener('mouseenter', () => {
            this.setHighlighted(index);
            item.style.backgroundColor = '#f8fafc';
            item.style.transform = 'translateX(4px)';
            item.style.borderLeft = '3px solid var(--primary-color, #667eea)';
        });

        item.addEventListener('mouseleave', () => {
            item.style.backgroundColor = '';
            item.style.transform = '';
            item.style.borderLeft = '';
        });

        item.addEventListener('click', () => {
            this.selectAdvancedSuggestion(suggestion, field, type);
        });

        return item;
    }

    // Función para obtener iconos avanzados según el tipo
    getAdvancedIcon(type, category) {
        const icons = {
            // Transporte
            airport: '✈️',
            station: '🚂',
            bus_station: '🚌',
            terminal: '🚢',
            metro: '🚇',
            port: '⚓',
            
            // Alojamiento
            hotel: '🏨',
            hostel: '🏠',
            resort: '🏖️',
            camping: '⛺',
            motel: '🏩',
            
            // Comida y bebida
            restaurant: '🍽️',
            cafe: '☕',
            bar: '🍺',
            fast_food: '🍟',
            pub: '🍻',
            bakery: '🥖',
            
            // Atracciones
            museum: '🏛️',
            monument: '🗿',
            castle: '🏰',
            church: '⛪',
            park: '🌳',
            beach: '🏖️',
            mountain: '⛰️',
            zoo: '🦁',
            aquarium: '🐠',
            
            // Lugares geográficos
            city: '🏙️',
            town: '🏘️',
            village: '🏡',
            country: '🌍',
            region: '🗺️',
            island: '🏝️',
            
            // Compras y servicios
            mall: '🛍️',
            market: '🏪',
            shop: '🏬',
            supermarket: '🛒',
            bank: '🏦',
            hospital: '🏥',
            pharmacy: '💊',
            school: '🏫',
            university: '🎓',
            
            // Entretenimiento
            cinema: '🎬',
            theater: '🎭',
            stadium: '🏟️',
            gym: '💪',
            spa: '🧘‍♀️',
            
            // Default
            place: '📍',
            attraction: '🎯',
            building: '🏢'
        };
        
        // Intentar con el tipo primero, luego con la categoría, luego default
        return icons[type] || icons[category] || icons.place;
    }

    // Crear badge de tipo
    createTypeBadge(type, source) {
        const colors = {
            hotel: '#059669',
            restaurant: '#dc2626',
            airport: '#2563eb',
            attraction: '#7c3aed',
            city: '#ea580c',
            place: '#6b7280'
        };
        
        const color = colors[type] || colors.place;
        
        return `<span style="
            background: ${color}20;
            color: ${color};
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 500;
            text-transform: uppercase;
        ">${type}</span>`;
    }

    // Crear display de rating
    createRatingDisplay(rating) {
        if (!rating || rating === 0) return '';
        
        const stars = '⭐'.repeat(Math.floor(rating));
        return `<span style="font-size: 11px; color: #f59e0b;">${stars}</span>`;
    }

    // Crear display de detalles
    createDetailsDisplay(details) {
        if (!details) return '';
        
        let detailsArray = [];
        
        if (details.population && details.population > 1000) {
            detailsArray.push(`👥 ${this.formatNumber(details.population)}`);
        }
        
        if (details.cuisine) {
            detailsArray.push(`🍽️ ${details.cuisine}`);
        }
        
        if (details.phone) {
            detailsArray.push(`📞 ${details.phone}`);
        }
        
        if (details.website) {
            detailsArray.push(`🌐 Web`);
        }
        
        if (detailsArray.length === 0) return '';
        
        return `<div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">
            ${detailsArray.slice(0, 3).join(' • ')}
        </div>`;
    }

    // Seleccionar sugerencia avanzada
    selectAdvancedSuggestion(suggestion, field, type) {
        console.log('🎯 Seleccionando ubicación súper completa:', suggestion);
        
        // Actualizar campo
        field.value = suggestion.fullName;
        
        // Actualizar coordenadas
        this.updateCoordinates(suggestion.lat, suggestion.lng, type);
        
        // Actualizar mapa con animación súper fluida
        this.updateMapWithAnimation(suggestion);
        
        // Limpiar sugerencias
        this.removeSuggestions();
        
        // Mostrar feedback visual
        this.showSelectionFeedback(field, suggestion);
        
        // Disparar evento personalizado con toda la información
        field.dispatchEvent(new CustomEvent('superLocationSelected', {
            detail: {
                location: suggestion,
                type: type,
                field: field.id
            }
        }));
    }

    // Actualizar mapa con animación súper fluida
    updateMapWithAnimation(suggestion) {
        if (typeof window.map === 'undefined' || !window.map) return;
        
        try {
            console.log('🗺️ Actualizando mapa a:', suggestion.name);
            
            // Animación fluida al nuevo punto
            window.map.flyTo([suggestion.lat, suggestion.lng], 16, {
                animate: true,
                duration: 1.5
            });
            
            // Remover marcador anterior
            if (window.currentMarker) {
                window.map.removeLayer(window.currentMarker);
            }
            
            // Crear nuevo marcador súper personalizado
            const customIcon = this.createCustomMapIcon(suggestion.type);
            
            window.currentMarker = L.marker([suggestion.lat, suggestion.lng], {
                icon: customIcon,
                draggable: true
            }).addTo(window.map);
            
            // Popup súper informativo
            const popupContent = this.createAdvancedPopup(suggestion);
            window.currentMarker.bindPopup(popupContent, {
                maxWidth: 300,
                className: 'super-popup'
            }).openPopup();
            
            // Event listener para arrastrar
            window.currentMarker.on('dragend', (e) => {
                const newPos = e.target.getLatLng();
                this.updateCoordinatesFromDrag(newPos.lat, newPos.lng);
            });
            
        } catch (error) {
            console.warn('Error actualizando mapa:', error);
        }
    }

    // Crear icono personalizado para el mapa
    createCustomMapIcon(type) {
        const icon = this.getAdvancedIcon(type);
        
        return L.divIcon({
            html: `
                <div style="
                    background: white;
                    border: 3px solid var(--primary-color, #667eea);
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 16px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    animation: mapMarkerBounce 0.6s ease-out;
                ">${icon}</div>
            `,
            className: 'custom-map-marker',
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });
    }

    // Crear popup avanzado para el mapa
    createAdvancedPopup(suggestion) {
        const icon = this.getAdvancedIcon(suggestion.type, suggestion.category);
        const rating = suggestion.rating ? `⭐ ${suggestion.rating}` : '';
        const category = suggestion.category ? `📁 ${suggestion.category}` : '';
        
        return `
            <div style="text-align: center; padding: 10px;">
                <div style="font-size: 24px; margin-bottom: 8px;">${icon}</div>
                <div style="font-weight: 600; font-size: 16px; color: #1e293b; margin-bottom: 6px;">
                    ${suggestion.name}
                </div>
                <div style="font-size: 12px; color: #64748b; margin-bottom: 8px; line-height: 1.4;">
                    ${suggestion.fullName}
                </div>
                ${rating ? `<div style="margin-bottom: 4px;">${rating}</div>` : ''}
                ${category ? `<div style="font-size: 11px; color: #94a3b8;">${category}</div>` : ''}
                <div style="font-size: 10px; color: #94a3b8; margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                    📍 ${suggestion.lat.toFixed(6)}, ${suggestion.lng.toFixed(6)}<br>
                    🌐 Fuente: ${suggestion.source}
                </div>
            </div>
        `;
    }

    // Mostrar feedback visual de selección
    showSelectionFeedback(field, suggestion) {
        // Cambiar temporalmente el borde del campo
        const originalBorder = field.style.border;
        field.style.border = '2px solid #10b981';
        field.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
        
        setTimeout(() => {
            field.style.border = originalBorder;
            field.style.boxShadow = '';
        }, 1000);
        
        // Mostrar toast de confirmación
        this.showLocationToast(suggestion);
    }

    // Mostrar toast de confirmación
    showLocationToast(suggestion) {
        const toast = document.createElement('div');
        toast.className = 'location-toast';
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
            z-index: 10000;
            animation: slideInFromRight 0.4s ease-out;
            max-width: 350px;
        `;
        
        const icon = this.getAdvancedIcon(suggestion.type);
        toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="font-size: 20px;">${icon}</div>
                <div>
                    <div style="font-weight: 600; margin-bottom: 2px;">Ubicación seleccionada</div>
                    <div style="font-size: 13px; opacity: 0.9;">${suggestion.name}</div>
                </div>
                <div style="margin-left: auto;">✅</div>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOutToRight 0.4s ease-in';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 400);
        }, 3000);
    }

    // Precargar ubicaciones populares
    preloadPopularLocations() {
        const popularLocations = [
            { name: 'París', fullName: 'París, Francia', lat: 48.8566, lng: 2.3522, type: 'city' },
            { name: 'Londres', fullName: 'Londres, Reino Unido', lat: 51.5074, lng: -0.1278, type: 'city' },
            { name: 'Nueva York', fullName: 'Nueva York, Estados Unidos', lat: 40.7128, lng: -74.0060, type: 'city' },
            { name: 'Tokio', fullName: 'Tokio, Japón', lat: 35.6762, lng: 139.6503, type: 'city' },
            { name: 'Roma', fullName: 'Roma, Italia', lat: 41.9028, lng: 12.4964, type: 'city' },
            { name: 'Barcelona', fullName: 'Barcelona, España', lat: 41.3851, lng: 2.1734, type: 'city' },
            { name: 'Madrid', fullName: 'Madrid, España', lat: 40.4168, lng: -3.7038, type: 'city' },
            { name: 'Ámsterdam', fullName: 'Ámsterdam, Países Bajos', lat: 52.3676, lng: 4.9041, type: 'city' },
            { name: 'Berlín', fullName: 'Berlín, Alemania', lat: 52.5200, lng: 13.4050, type: 'city' },
            { name: 'Bogotá', fullName: 'Bogotá, Colombia', lat: 4.7110, lng: -74.0721, type: 'city' },
            { name: 'Buenos Aires', fullName: 'Buenos Aires, Argentina', lat: -34.6118, lng: -58.3960, type: 'city' },
            { name: 'Lima', fullName: 'Lima, Perú', lat: -12.0464, lng: -77.0428, type: 'city' },
            { name: 'Santiago', fullName: 'Santiago, Chile', lat: -33.4489, lng: -70.6693, type: 'city' },
            { name: 'São Paulo', fullName: 'São Paulo, Brasil', lat: -23.5505, lng: -46.6333, type: 'city' },
            { name: 'Medellín', fullName: 'Medellín, Colombia', lat: 6.2442, lng: -75.5812, type: 'city' },
            // Aeropuertos importantes
            { name: 'Aeropuerto Madrid Barajas', fullName: 'Aeropuerto Adolfo Suárez Madrid-Barajas, España', lat: 40.4983, lng: -3.5676, type: 'airport' },
            { name: 'Aeropuerto Charles de Gaulle', fullName: 'Aeropuerto Charles de Gaulle, París, Francia', lat: 49.0097, lng: 2.5479, type: 'airport' },
            { name: 'Aeropuerto Heathrow', fullName: 'Aeropuerto de Londres-Heathrow, Reino Unido', lat: 51.4700, lng: -0.4543, type: 'airport' },
            { name: 'Aeropuerto El Dorado', fullName: 'Aeropuerto Internacional El Dorado, Bogotá, Colombia', lat: 4.7016, lng: -74.1469, type: 'airport' }
        ];
        
        // Guardar en cache
        popularLocations.forEach(location => {
            location.source = 'Popular';
            location.importance = 0.9;
        });
        
        this.cache.set('popular_locations', popularLocations);
    }

    // Mostrar sugerencias populares
    showPopularSuggestions(field, type) {
        const popular = this.cache.get('popular_locations') || [];
        const filtered = type === 'salida' || type === 'llegada' ? 
            popular.filter(loc => loc.type === 'airport' || loc.type === 'city').slice(0, 8) :
            popular.slice(0, 10);
        
        this.showAdvancedSuggestions(filtered, field, type);
    }

    // Mostrar mensaje de sin resultados
    showNoResultsMessage(field) {
        this.currentSuggestionsList = document.createElement('div');
        this.currentSuggestionsList.className = 'no-results-message';
        this.currentSuggestionsList.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 12px 12px;
            padding: 20px;
            text-align: center;
            color: #64748b;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        `;
        
        this.currentSuggestionsList.innerHTML = `
            <div style="font-size: 48px; margin-bottom: 12px; opacity: 0.5;">🔍</div>
            <div style="font-weight: 500; margin-bottom: 8px;">No se encontraron resultados</div>
            <div style="font-size: 13px;">Intenta con otros términos de búsqueda</div>
            <div style="margin-top: 12px;">
                <button onclick="window.superLocationAutocomplete.showPopularSuggestions(document.getElementById('${field.id}'), '${this.currentType}')" 
                        style="background: var(--primary-color, #667eea); color: white; border: none; padding: 8px 16px; border-radius: 20px; font-size: 12px; cursor: pointer;">
                    Ver lugares populares
                </button>
            </div>
        `;
        
        field.parentElement.appendChild(this.currentSuggestionsList);
    }

    // Mostrar loading avanzado
    showAdvancedLoading(field) {
        this.hideAdvancedLoading(field);
        
        const loader = document.createElement('div');
        loader.className = 'super-location-loader';
        loader.style.cssText = `
            position: absolute;
            right: 35px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
        `;
        
        loader.innerHTML = `
            <div style="
                width: 16px;
                height: 16px;
                border: 2px solid #e2e8f0;
                border-top: 2px solid var(--primary-color, #667eea);
                border-radius: 50%;
                animation: superSpin 0.8s linear infinite;
            "></div>
        `;
        
        field.parentElement.appendChild(loader);
    }

    // Ocultar loading avanzado
    hideAdvancedLoading(field) {
        const loader = field.parentElement.querySelector('.super-location-loader');
        if (loader) loader.remove();
    }

    // Manejar focus avanzado
    handleAdvancedFocus(event, type) {
        const field = event.target;
        const query = field.value.trim();
        
        this.currentField = field;
        this.currentType = type;

        // Cambiar estilo del campo
        field.style.borderColor = 'var(--primary-color, #667eea)';
        field.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.1)';

        if (query.length >= 2) {
            const cacheKey = `${query.toLowerCase()}_${type}`;
            if (this.cache.has(cacheKey)) {
                this.showAdvancedSuggestions(this.cache.get(cacheKey), field, type);
            }
        } else if (query.length === 0) {
            this.showPopularSuggestions(field, type);
        }
    }

    // Manejar blur avanzado
    handleAdvancedBlur(event) {
        const field = event.target;
        
        // Restaurar estilo del campo
        field.style.borderColor = '';
        field.style.boxShadow = '';
        
        // Delay para permitir clicks en sugerencias
        setTimeout(() => {
            this.removeSuggestions();
            this.hideAdvancedLoading(field);
        }, 200);
    }

    // Manejar teclado avanzado
    handleAdvancedKeydown(event, type) {
        if (!this.currentSuggestionsList) {
            // Si no hay sugerencias y presiona flecha abajo, mostrar populares
            if (event.key === 'ArrowDown' && event.target.value.trim() === '') {
                event.preventDefault();
                this.showPopularSuggestions(event.target, type);
                return;
            }
            return;
        }

        switch(event.key) {
            case 'ArrowDown':
                event.preventDefault();
                this.navigateAdvanced(1);
                break;
            case 'ArrowUp':
                event.preventDefault();
                this.navigateAdvanced(-1);
                break;
            case 'Enter':
                event.preventDefault();
                this.selectHighlighted(event.target, type);
                break;
            case 'Escape':
                this.removeSuggestions();
                break;
            case 'Tab':
                // Permitir tab normal pero cerrar sugerencias
                this.removeSuggestions();
                break;
        }
    }

    // Navegación avanzada
    navigateAdvanced(direction) {
        if (!this.currentSuggestionsList) return;
        
        const items = this.currentSuggestionsList.querySelectorAll('.super-suggestion-item');
        const maxIndex = items.length - 1;
        
        if (direction === 1) { // Abajo
            this.highlightedIndex = this.highlightedIndex < maxIndex ? this.highlightedIndex + 1 : 0;
        } else { // Arriba
            this.highlightedIndex = this.highlightedIndex > 0 ? this.highlightedIndex - 1 : maxIndex;
        }
        
        this.setHighlighted(this.highlightedIndex);
    }

    // Establecer elemento resaltado avanzado
    setHighlighted(index) {
        if (!this.currentSuggestionsList) return;
        
        const items = this.currentSuggestionsList.querySelectorAll('.super-suggestion-item');
        items.forEach((item, i) => {
            if (i === index) {
                item.style.backgroundColor = '#f8fafc';
                item.style.transform = 'translateX(4px)';
                item.style.borderLeft = '3px solid var(--primary-color, #667eea)';
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                this.highlightedIndex = index;
            } else {
                item.style.backgroundColor = '';
                item.style.transform = '';
                item.style.borderLeft = '';
            }
        });
    }

    // Seleccionar elemento resaltado
    selectHighlighted(field, type) {
        if (this.highlightedIndex === -1) return;
        
        const items = this.currentSuggestionsList.querySelectorAll('.super-suggestion-item');
        const item = items[this.highlightedIndex];
        if (item) {
            item.click();
        }
    }

    // Crear footer avanzado
    createAdvancedFooter() {
        const footer = document.createElement('div');
        footer.style.cssText = `
            padding: 10px 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-top: 1px solid #e2e8f0;
            font-size: 11px;
            color: #64748b;
            text-align: center;
        `;
        
        footer.innerHTML = `
            <div style="display: flex; justify-content: center; align-items: center; gap: 16px;">
                <span>↑↓ Navegar</span>
                <span>↵ Seleccionar</span>
                <span>⎋ Cerrar</span>
                <span style="color: #94a3b8;">|</span>
                <span>🌍 Búsqueda global en tiempo real</span>
            </div>
        `;
        
        return footer;
    }

    // Agregar CSS súper avanzado
    addAdvancedCSS() {
        if (document.querySelector('#super-location-autocomplete-css')) return;
        
        const style = document.createElement('style');
        style.id = 'super-location-autocomplete-css';
        style.textContent = `
            @keyframes slideDown {
                from { opacity: 0; transform: translateY(-15px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            @keyframes slideInFromRight {
                from { opacity: 0; transform: translateX(100%); }
                to { opacity: 1; transform: translateX(0); }
            }
            
            @keyframes slideOutToRight {
                from { opacity: 1; transform: translateX(0); }
                to { opacity: 0; transform: translateX(100%); }
            }
            
            @keyframes superSpin {
                from { transform: translateY(-50%) rotate(0deg); }
                to { transform: translateY(-50%) rotate(360deg); }
            }
            
            @keyframes mapMarkerBounce {
                0% { transform: translateY(-20px) scale(0.8); opacity: 0; }
                50% { transform: translateY(-5px) scale(1.1); opacity: 0.8; }
                100% { transform: translateY(0) scale(1); opacity: 1; }
            }
            
            .super-location-suggestions::-webkit-scrollbar {
                width: 8px;
            }
            
            .super-location-suggestions::-webkit-scrollbar-track {
                background: #f1f5f9;
                border-radius: 4px;
            }
            
            .super-location-suggestions::-webkit-scrollbar-thumb {
                background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
                border-radius: 4px;
            }
            
            .super-location-suggestions::-webkit-scrollbar-thumb:hover {
                background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
            }
            
            .super-suggestion-item:last-of-type {
                border-bottom: none;
            }
            
            .super-popup .leaflet-popup-content-wrapper {
                border-radius: 12px;
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            }
            
            .custom-map-marker {
                filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
            }
        `;
        
        document.head.appendChild(style);
    }

    // Funciones auxiliares
    extractBestName(item) {
        // Si tiene namedetails, usar name
        if (item.namedetails && item.namedetails.name) {
            return item.namedetails.name;
        }
        
        // Si no, tomar la primera parte del display_name
        if (item.display_name) {
            return item.display_name.split(',')[0].trim();
        }
        
        // Fallback al nombre directo
        return item.name || 'Ubicación sin nombre';
    }
    // Función para crear un debounce (evitar demasiadas llamadas)
    createDebounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Función para verificar si el navegador soporta las APIs necesarias
    checkBrowserSupport() {
        const support = {
            fetch: typeof fetch !== 'undefined',
            geolocation: 'geolocation' in navigator,
            localStorage: typeof Storage !== 'undefined',
            promises: typeof Promise !== 'undefined'
        };
        
        const allSupported = Object.values(support).every(Boolean);
        
        if (!allSupported) {
            console.warn('⚠️ Algunas funciones del autocompletado pueden no funcionar:', support);
        }
        
        return allSupported;
    }

    // Función para detectar el tipo de búsqueda basado en el input
    detectSearchType(query) {
        const queryLower = query.toLowerCase();
        
        // Detectar aeropuertos
        if (queryLower.includes('aeropuerto') || queryLower.includes('airport')) {
            return 'airport';
        }
        
        // Detectar hoteles
        if (queryLower.includes('hotel') || queryLower.includes('hostel')) {
            return 'hotel';
        }
        
        // Detectar restaurantes
        if (queryLower.includes('restaurante') || queryLower.includes('restaurant') || 
            queryLower.includes('cafe') || queryLower.includes('comida')) {
            return 'restaurant';
        }
        
        // Detectar atracciones
        if (queryLower.includes('museo') || queryLower.includes('museum') ||
            queryLower.includes('monumento') || queryLower.includes('torre') ||
            queryLower.includes('parque') || queryLower.includes('playa')) {
            return 'attraction';
        }
        
        return 'general';
    }

    // Función para limpiar y validar texto de entrada
    sanitizeInput(input) {
        if (typeof input !== 'string') return '';
        
        return input
            .trim()
            .replace(/[<>]/g, '') // Remover < y > para prevenir XSS básico
            .substring(0, 200);   // Limitar longitud
    }
    // Función para validar coordenadas
    isValidCoordinate(lat, lng) {
        return (
            typeof lat === 'number' && 
            typeof lng === 'number' &&
            !isNaN(lat) && 
            !isNaN(lng) &&
            lat >= -90 && lat <= 90 &&
            lng >= -180 && lng <= 180
        );
    }

    classifyAdvanced(item) {
        const type = (item.type || '').toLowerCase();
        const category = (item.category || '').toLowerCase();
        const amenity = (item.amenity || '').toLowerCase();
        const tourism = (item.tourism || '').toLowerCase();
        
        // Transporte
        if (type.includes('aerodrome') || category.includes('airport')) return 'airport';
        if (type.includes('railway') || type.includes('station')) return 'station';
        if (type.includes('bus') && type.includes('station')) return 'bus_station';
        
        // Alojamiento
        if (tourism === 'hotel' || amenity === 'hotel') return 'hotel';
        if (tourism === 'hostel') return 'hostel';
        if (tourism === 'resort') return 'resort';
        if (tourism === 'camp_site') return 'camping';
        
        // Comida
        if (amenity === 'restaurant') return 'restaurant';
        if (amenity === 'cafe') return 'cafe';
        if (amenity === 'bar' || amenity === 'pub') return 'bar';
        if (amenity === 'fast_food') return 'fast_food';
        
        // Atracciones
        if (tourism === 'museum') return 'museum';
        if (tourism === 'monument') return 'monument';
        if (amenity === 'place_of_worship') return 'church';
        if (type === 'castle') return 'castle';
        
        // Lugares
        if (type === 'city' || category === 'place') return 'city';
        if (type === 'town') return 'town';
        if (type === 'village') return 'village';
        if (type === 'country') return 'country';
        
        return 'place';
    }

    getCategory(item) {
        return item.category || item.type || item.class || 'place';
    }

    extractDetails(item) {
        const details = {};
        
        if (item.extratags) {
            details.phone = item.extratags.phone;
            details.website = item.extratags.website;
            details.cuisine = item.extratags.cuisine;
            details.opening_hours = item.extratags.opening_hours;
        }
        
        return details;
    }

    estimateRating(item) {
        // Estimar rating basado en importancia y características
        if (item.importance > 0.8) return 5;
        if (item.importance > 0.6) return 4;
        if (item.importance > 0.4) return 3;
        return 0;
    }

    buildFullName(properties) {
        const parts = [
            properties.name,
            properties.city,
            properties.state,
            properties.country
        ].filter(Boolean);
        
        return parts.join(', ');
    }

    classifyPhoton(properties) {
        const type = (properties.osm_value || '').toLowerCase();
        const key = (properties.osm_key || '').toLowerCase();
        
        if (key === 'tourism') return this.mapTourismType(type);
        if (key === 'amenity') return this.mapAmenityType(type);
        if (key === 'place') return this.mapPlaceType(type);
        
        return 'place';
    }

    mapTourismType(type) {
        const mapping = {
            hotel: 'hotel',
            hostel: 'hostel',
            museum: 'museum',
            monument: 'monument',
            attraction: 'attraction'
        };
        return mapping[type] || 'attraction';
    }

    mapAmenityType(type) {
        const mapping = {
            restaurant: 'restaurant',
            cafe: 'cafe',
            hotel: 'hotel',
            hospital: 'hospital',
            school: 'school'
        };
        return mapping[type] || 'amenity';
    }

    mapPlaceType(type) {
        const mapping = {
            city: 'city',
            town: 'town',
            village: 'village',
            country: 'country'
        };
        return mapping[type] || 'place';
    }

    buildOverpassName(tags) {
        const parts = [tags.name];
        if (tags['addr:city']) parts.push(tags['addr:city']);
        if (tags['addr:country']) parts.push(tags['addr:country']);
        return parts.filter(Boolean).join(', ');
    }

    classifyOverpass(tags) {
        if (tags.tourism) return tags.tourism;
        if (tags.amenity) return tags.amenity;
        if (tags.leisure) return tags.leisure;
        return 'place';
    }

    parseRating(tags) {
        const stars = tags.stars || tags['stars:rating'];
        return stars ? parseInt(stars) : 0;
    }

    buildGeoNamesFullName(item) {
        const parts = [item.name];
        if (item.adminName1) parts.push(item.adminName1);
        if (item.countryName) parts.push(item.countryName);
        return parts.join(', ');
    }

    classifyGeoNames(item) {
        const fcl = item.fcl;
        const fcode = item.fcode;
        
        if (fcl === 'P') return 'city'; // Populated place
        if (fcl === 'A') return 'region'; // Administrative
        if (fcl === 'S') return 'attraction'; // Spot
        if (fcl === 'T') return 'terrain'; // Terrain
        
        return 'place';
    }

    updateCoordinates(lat, lng, type) {
        if (type === 'salida') {
            this.setFieldValue('lat_salida', lat);
            this.setFieldValue('lng_salida', lng);
        } else if (type === 'llegada') {
            this.setFieldValue('lat_llegada', lat);
            this.setFieldValue('lng_llegada', lng);
        } else {
            this.setFieldValue('latitud', lat);
            this.setFieldValue('longitud', lng);
        }
    }

    updateCoordinatesFromDrag(lat, lng) {
        if (this.currentType === 'salida') {
            this.setFieldValue('lat_salida', lat);
            this.setFieldValue('lng_salida', lng);
        } else if (this.currentType === 'llegada') {
            this.setFieldValue('lat_llegada', lat);
            this.setFieldValue('lng_llegada', lng);
        } else {
            this.setFieldValue('latitud', lat);
            this.setFieldValue('longitud', lng);
        }
    }

    setFieldValue(fieldId, value) {
        const field = document.getElementById(fieldId);
        if (field && value !== undefined && value !== null) {
            field.value = typeof value === 'number' ? value.toFixed(6) : value;
        }
    }

    

    // Calcular distancia de Levenshtein (similitud entre strings)
    levenshteinDistance(str1, str2) {
        const matrix = [];
        
        // Inicializar primera fila y columna
        for (let i = 0; i <= str2.length; i++) {
            matrix[i] = [i];
        }
        
        for (let j = 0; j <= str1.length; j++) {
            matrix[0][j] = j;
        }
        
        // Llenar la matriz
        for (let i = 1; i <= str2.length; i++) {
            for (let j = 1; j <= str1.length; j++) {
                if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
                    // Los caracteres son iguales
                    matrix[i][j] = matrix[i - 1][j - 1];
                } else {
                    // Los caracteres son diferentes, tomar el mínimo
                    matrix[i][j] = Math.min(
                        matrix[i - 1][j - 1] + 1, // Substitución
                        matrix[i][j - 1] + 1,     // Inserción
                        matrix[i - 1][j] + 1      // Eliminación
                    );
                }
            }
        }
        
        return matrix[str2.length][str1.length];
    }


    // Función auxiliar para formatear números grandes
    formatNumber(num) {
        if (!num || isNaN(num)) return '0';
        
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }

    // Remover sugerencias
    removeSuggestions() {
        if (this.currentSuggestionsList) {
            this.currentSuggestionsList.remove();
            this.currentSuggestionsList = null;
            this.highlightedIndex = -1;
        }
    }

    // Limpiar cache
    clearCache() {
        this.cache.clear();
        console.log('🧹 Cache de ubicaciones limpiado');
    }

    // Destruir instancia
    destroy() {
        this.removeSuggestions();
        this.clearCache();
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
        
        // Remover event listeners
        document.querySelectorAll('[data-autocomplete-setup]').forEach(field => {
            field.removeAttribute('data-autocomplete-setup');
        });
        
        console.log('🗑️ Super Location Autocomplete destruido');
    }

    // Método para debugging
    getDebugInfo() {
        return {
            cacheSize: this.cache.size,
            isLoading: this.isLoading,
            currentField: this.currentField?.id,
            currentType: this.currentType,
            highlightedIndex: this.highlightedIndex,
            hasSuggestions: !!this.currentSuggestionsList
        };
    }
}

// =====================================
// INICIALIZACIÓN Y EXPORTACIÓN
// =====================================

// Crear instancia global
window.superLocationAutocomplete = new SuperLocationAutocomplete();

// Funciones de integración para el sistema existente
function initializeSuperLocationAutocomplete() {
    console.log('🚀 Inicializando SUPER autocompletado...');
    setTimeout(() => {
        window.superLocationAutocomplete.initialize();
    }, 200);
}

function cleanupSuperLocationAutocomplete() {
    if (window.superLocationAutocomplete) {
        window.superLocationAutocomplete.removeSuggestions();
    }
}

// Event listeners globales
document.addEventListener('DOMContentLoaded', function() {
    console.log('🌍 Super Location Autocomplete System Ready!');
    
    // Auto-detectar y configurar campos existentes
    setTimeout(() => {
        const fields = document.querySelectorAll('#ubicacion, #lugar_salida, #lugar_llegada');
        if (fields.length > 0) {
            initializeSuperLocationAutocomplete();
        }
    }, 1000);
});

// Event listener para ubicaciones seleccionadas
document.addEventListener('superLocationSelected', function(event) {
    const data = event.detail;
    console.log('🎯 SUPER ubicación seleccionada:', data);
    
    // Aquí puedes agregar lógica adicional
    // Por ejemplo: analytics, validaciones, actualizaciones de UI, etc.
});

// Función para debugging (accesible desde consola)
window.debugLocationAutocomplete = function() {
    return window.superLocationAutocomplete.getDebugInfo();
};

// Función para limpiar cache manualmente
window.clearLocationCache = function() {
    window.superLocationAutocomplete.clearCache();
};

console.log('✅ Super Location Autocomplete System Loaded Successfully!');