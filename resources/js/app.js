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
    abierto: false,
    f: { g: [], p: [], desde: '', hasta: '', mc: 0 },
    o: '',
    pagina: 1,
    mas: { local: false, remoto: false },
    cargandoMas: false,

    init() {
        const u = new URLSearchParams(location.search);
        this.f.g = u.getAll('g[]');
        this.f.p = u.getAll('p[]');
        this.f.desde = u.get('desde') || '';
        this.f.hasta = u.get('hasta') || '';
        this.f.mc = parseInt(u.get('mc') || '0', 10) || 0;
        this.o = u.get('o') || '';
        this.abierto = this.activos > 0;
        if (this.q || this.activos) this.lanzar();
    },

    get activos() {
        return this.f.g.length + this.f.p.length + (this.f.desde ? 1 : 0) + (this.f.hasta ? 1 : 0) + (this.f.mc ? 1 : 0) + (this.o ? 1 : 0);
    },

    alternar(lista, v) {
        const i = this.f[lista].indexOf(v);
        if (i >= 0) this.f[lista].splice(i, 1); else this.f[lista].push(v);
        this.lanzar();
    },

    puntuar(n) {
        this.f.mc = this.f.mc === n ? 0 : n;
        this.lanzar();
    },

    ordenar(o) {
        this.o = this.o === o ? '' : o;
        this.lanzar();
    },

    ordenados(lista) {
        const num = (v, d) => (v === null || v === undefined ? d : v);
        const por = {
            valoracion: (a, b) => num(b.mc, -1) - num(a.mc, -1) || num(b.rating, -1) - num(a.rating, -1),
            anio: (a, b) => (b.date || '').localeCompare(a.date || ''),
            nombre: (a, b) => a.name.localeCompare(b.name, 'es'),
        }[this.o];
        return por ? [...lista].sort(por) : lista;
    },

    limpiar() {
        this.f = { g: [], p: [], desde: '', hasta: '', mc: 0 };
        this.o = '';
        this.lanzar();
    },

    params() {
        const u = new URLSearchParams();
        const q = this.q.trim();
        if (q) u.set('q', q);
        this.f.g.forEach((v) => u.append('g[]', v));
        this.f.p.forEach((v) => u.append('p[]', v));
        if (this.f.desde) u.set('desde', this.f.desde);
        if (this.f.hasta) u.set('hasta', this.f.hasta);
        if (this.f.mc) u.set('mc', this.f.mc);
        if (this.o) u.set('o', this.o);
        return u;
    },

    async lanzar() {
        const q = this.q.trim();
        const id = ++this.id;
        if (this.ctl) this.ctl.abort();
        this.ctl = new AbortController();
        const signal = this.ctl.signal;
        this.remotoFallo = false;

        if (q.length < 2 && !this.activos) {
            this.items = [];
            this.buscado = false;
            this.cargandoRemoto = false;
            history.replaceState(null, '', location.pathname);
            return;
        }
        const base = this.params();
        history.replaceState(null, '', '?' + base.toString());

        this.pagina = 1;
        this.mas = { local: false, remoto: false };
        this.cargandoMas = false;

        const pedir = async (src, pag = 1) => {
            const u = new URLSearchParams(base);
            u.set('src', src);
            u.set('page', pag);
            const r = await fetch('/api/buscar?' + u.toString(), { signal, headers: { Accept: 'application/json' } });
            return r.json();
        };

        this.cargandoRemoto = true;
        pedir('local').then((d) => {
            if (id !== this.id) return;
            const locales = new Set(d.items.map((i) => i.slug));
            this.items = this.ordenados([...d.items, ...this.items.filter((i) => !locales.has(i.slug))]);
            this.mas.local = !!d.more;
            this.buscado = true;
        }).catch(() => {});

        pedir('remoto').then((d) => {
            if (id !== this.id) return;
            if (!d.ok) { this.remotoFallo = true; return; }
            this.mas.remoto = !!d.more;
            const vistos = new Set(this.items.map((i) => i.slug));
            this.items = this.ordenados([...this.items, ...d.items.filter((i) => !vistos.has(i.slug))]);
            this.buscado = true;
        }).catch((e) => {
            if (e.name !== 'AbortError' && id === this.id) this.remotoFallo = true;
        }).finally(() => {
            if (id === this.id) this.cargandoRemoto = false;
        });
        this.pedirPagina = pedir;
    },

    async cargarMas() {
        if (this.cargandoMas || !this.pedirPagina) return;
        const id = this.id;
        this.cargandoMas = true;
        const pag = ++this.pagina;
        const tareas = [];
        for (const src of ['local', 'remoto']) {
            if (!this.mas[src]) continue;
            tareas.push(this.pedirPagina(src, pag).then((d) => {
                if (id !== this.id) return;
                this.mas[src] = !!(d.ok && d.more);
                const vistos = new Set(this.items.map((i) => i.slug));
                this.items = this.ordenados([...this.items, ...d.items.filter((i) => !vistos.has(i.slug))]);
            }).catch(() => { this.mas[src] = false; }));
        }
        await Promise.all(tareas);
        if (id === this.id) this.cargandoMas = false;
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
