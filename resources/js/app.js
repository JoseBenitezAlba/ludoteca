import Alpine from 'alpinejs';

window.Alpine = Alpine;

/** Buscador: primero la base local (instantaneo) y a la vez RAWG con su propio timeout. */
Alpine.data('buscador', (inicial = '') => ({
    q: inicial,
    items: [],
    cargandoRemoto: false,
    remotoFallo: false,
    buscado: false,
    ctl: null,
    id: 0,

    init() {
        if (this.q) this.lanzar();
    },

    async lanzar() {
        const q = this.q.trim();
        const id = ++this.id;
        if (this.ctl) this.ctl.abort();
        this.ctl = new AbortController();
        const signal = this.ctl.signal;
        this.remotoFallo = false;

        if (q.length < 2) {
            this.items = [];
            this.buscado = false;
            this.cargandoRemoto = false;
            return;
        }
        history.replaceState(null, '', '?q=' + encodeURIComponent(q));

        const pedir = async (src) => {
            const r = await fetch(`/api/buscar?src=${src}&q=${encodeURIComponent(q)}`, { signal, headers: { Accept: 'application/json' } });
            return r.json();
        };

        this.cargandoRemoto = true;
        pedir('local').then((d) => {
            if (id !== this.id) return;
            this.items = d.items;
            this.buscado = true;
        }).catch(() => {});

        pedir('remoto').then((d) => {
            if (id !== this.id) return;
            if (!d.ok) { this.remotoFallo = true; return; }
            const vistos = new Set(this.items.map((i) => i.slug));
            this.items = [...this.items, ...d.items.filter((i) => !vistos.has(i.slug))];
            this.buscado = true;
        }).catch((e) => {
            if (e.name !== 'AbortError' && id === this.id) this.remotoFallo = true;
        }).finally(() => {
            if (id === this.id) this.cargandoRemoto = false;
        });
    },
}));

/** Selector de estrellas con medias estrellas (valor 1-10, 0 = sin nota). */
Alpine.data('estrellas', (valor = 0) => ({
    v: valor,
    poner(n) { this.v = this.v === n ? 0 : n; },
    get texto() { return this.v ? (this.v / 2).toString().replace('.', ',') + ' / 5' : 'Sin nota'; },
}));

Alpine.start();

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}
