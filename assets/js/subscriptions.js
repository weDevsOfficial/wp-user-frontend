/**
* @vue/shared v3.5.4
* (c) 2018-present Yuxi (Evan) You and Vue contributors
* @license MIT
**/
/*! #__NO_SIDE_EFFECTS__ */
// @__NO_SIDE_EFFECTS__
function Oi(e) {
  const t = /* @__PURE__ */ Object.create(null);
  for (const n of e.split(",")) t[n] = 1;
  return (n) => n in t;
}
const Xe = {}, tr = [], Dn = () => {
}, pf = () => false, Ul = (e) => e.charCodeAt(0) === 111 && e.charCodeAt(1) === 110 && (e.charCodeAt(2) > 122 || e.charCodeAt(2) < 97), Pi = (e) => e.startsWith("onUpdate:"), Ct = Object.assign, $i = (e, t) => {
  const n = e.indexOf(t);
  n > -1 && e.splice(n, 1);
}, ff = Object.prototype.hasOwnProperty, We = (e, t) => ff.call(e, t), $e = Array.isArray, nr = (e) => rl(e) === "[object Map]", ic = (e) => rl(e) === "[object Set]", vf = (e) => rl(e) === "[object RegExp]", Ee = (e) => typeof e == "function", rt = (e) => typeof e == "string", wa = (e) => typeof e == "symbol", et = (e) => e !== null && typeof e == "object", sc = (e) => (et(e) || Ee(e)) && Ee(e.then) && Ee(e.catch), uc = Object.prototype.toString, rl = (e) => uc.call(e), mf = (e) => rl(e).slice(8, -1), cc = (e) => rl(e) === "[object Object]", Ri = (e) => rt(e) && e !== "NaN" && e[0] !== "-" && "" + parseInt(e, 10) === e, Er = /* @__PURE__ */ Oi(",key,ref,ref_for,ref_key,onVnodeBeforeMount,onVnodeMounted,onVnodeBeforeUpdate,onVnodeUpdated,onVnodeBeforeUnmount,onVnodeUnmounted"), Gl = (e) => {
  const t = /* @__PURE__ */ Object.create(null);
  return (n) => t[n] || (t[n] = e(n));
}, hf = /-(\w)/g, wn = Gl((e) => e.replace(hf, (t, n) => n ? n.toUpperCase() : "")), gf = /\B([A-Z])/g, ya = Gl((e) => e.replace(gf, "-$1").toLowerCase()), Ql = Gl((e) => e.charAt(0).toUpperCase() + e.slice(1)), Ao = Gl((e) => e ? `on${Ql(e)}` : ""), ma = (e, t) => !Object.is(e, t), Nr = (e, ...t) => {
  for (let n = 0; n < e.length; n++) e[n](...t);
}, dc = (e, t, n, a = false) => {
  Object.defineProperty(e, t, { configurable: true, enumerable: false, writable: a, value: n });
}, wf = (e) => {
  const t = parseFloat(e);
  return isNaN(t) ? e : t;
}, yf = (e) => {
  const t = rt(e) ? Number(e) : NaN;
  return isNaN(t) ? e : t;
};
let Ps;
const pc = () => Ps || (Ps = typeof globalThis < "u" ? globalThis : typeof self < "u" ? self : typeof window < "u" ? window : typeof global < "u" ? global : {});
function Lt(e) {
  if ($e(e)) {
    const t = {};
    for (let n = 0; n < e.length; n++) {
      const a = e[n], r = rt(a) ? kf(a) : Lt(a);
      if (r) for (const l in r) t[l] = r[l];
    }
    return t;
  } else if (rt(e) || et(e)) return e;
}
const bf = /;(?![^(]*\))/g, _f = /:([^]+)/, xf = /\/\*[^]*?\*\//g;
function kf(e) {
  const t = {};
  return e.replace(xf, "").split(bf).forEach((n) => {
    if (n) {
      const a = n.split(_f);
      a.length > 1 && (t[a[0].trim()] = a[1].trim());
    }
  }), t;
}
function pe(e) {
  let t = "";
  if (rt(e)) t = e;
  else if ($e(e)) for (let n = 0; n < e.length; n++) {
    const a = pe(e[n]);
    a && (t += a + " ");
  }
  else if (et(e)) for (const n in e) e[n] && (t += n + " ");
  return t.trim();
}
function Ot(e) {
  if (!e) return null;
  let { class: t, style: n } = e;
  return t && !rt(t) && (e.class = pe(t)), n && (e.style = Lt(n)), e;
}
const Sf = "itemscope,allowfullscreen,formnovalidate,ismap,nomodule,novalidate,readonly", Cf = /* @__PURE__ */ Oi(Sf);
function fc(e) {
  return !!e || e === "";
}
const vc = (e) => !!(e && e.__v_isRef === true), ge = (e) => rt(e) ? e : e == null ? "" : $e(e) || et(e) && (e.toString === uc || !Ee(e.toString)) ? vc(e) ? ge(e.value) : JSON.stringify(e, mc, 2) : String(e), mc = (e, t) => vc(t) ? mc(e, t.value) : nr(t) ? { [`Map(${t.size})`]: [...t.entries()].reduce((n, [a, r], l) => (n[Do(a, l) + " =>"] = r, n), {}) } : ic(t) ? { [`Set(${t.size})`]: [...t.values()].map((n) => Do(n)) } : wa(t) ? Do(t) : et(t) && !$e(t) && !cc(t) ? String(t) : t, Do = (e, t = "") => {
  var n;
  return wa(e) ? `Symbol(${(n = e.description) != null ? n : t})` : e;
};
/**
* @vue/reactivity v3.5.4
* (c) 2018-present Yuxi (Evan) You and Vue contributors
* @license MIT
**/
let Ht;
class hc {
  constructor(t = false) {
    this.detached = t, this._active = true, this.effects = [], this.cleanups = [], this._isPaused = false, this.parent = Ht, !t && Ht && (this.index = (Ht.scopes || (Ht.scopes = [])).push(this) - 1);
  }
  get active() {
    return this._active;
  }
  pause() {
    if (this._active) {
      this._isPaused = true;
      let t, n;
      if (this.scopes) for (t = 0, n = this.scopes.length; t < n; t++) this.scopes[t].pause();
      for (t = 0, n = this.effects.length; t < n; t++) this.effects[t].pause();
    }
  }
  resume() {
    if (this._active && this._isPaused) {
      this._isPaused = false;
      let t, n;
      if (this.scopes) for (t = 0, n = this.scopes.length; t < n; t++) this.scopes[t].resume();
      for (t = 0, n = this.effects.length; t < n; t++) this.effects[t].resume();
    }
  }
  run(t) {
    if (this._active) {
      const n = Ht;
      try {
        return Ht = this, t();
      } finally {
        Ht = n;
      }
    }
  }
  on() {
    Ht = this;
  }
  off() {
    Ht = this.parent;
  }
  stop(t) {
    if (this._active) {
      let n, a;
      for (n = 0, a = this.effects.length; n < a; n++) this.effects[n].stop();
      for (n = 0, a = this.cleanups.length; n < a; n++) this.cleanups[n]();
      if (this.scopes) for (n = 0, a = this.scopes.length; n < a; n++) this.scopes[n].stop(true);
      if (!this.detached && this.parent && !t) {
        const r = this.parent.scopes.pop();
        r && r !== this && (this.parent.scopes[this.index] = r, r.index = this.index);
      }
      this.parent = void 0, this._active = false;
    }
  }
}
function gc(e) {
  return new hc(e);
}
function Ei() {
  return Ht;
}
function wc(e, t = false) {
  Ht && Ht.cleanups.push(e);
}
let Qe;
const Lo = /* @__PURE__ */ new WeakSet();
class yc {
  constructor(t) {
    this.fn = t, this.deps = void 0, this.depsTail = void 0, this.flags = 5, this.nextEffect = void 0, this.cleanup = void 0, this.scheduler = void 0, Ht && Ht.active && Ht.effects.push(this);
  }
  pause() {
    this.flags |= 64;
  }
  resume() {
    this.flags & 64 && (this.flags &= -65, Lo.has(this) && (Lo.delete(this), this.trigger()));
  }
  notify() {
    this.flags & 2 && !(this.flags & 32) || this.flags & 8 || (this.flags |= 8, this.nextEffect = Ir, Ir = this);
  }
  run() {
    if (!(this.flags & 1)) return this.fn();
    this.flags |= 2, $s(this), _c(this);
    const t = Qe, n = mn;
    Qe = this, mn = true;
    try {
      return this.fn();
    } finally {
      xc(this), Qe = t, mn = n, this.flags &= -3;
    }
  }
  stop() {
    if (this.flags & 1) {
      for (let t = this.deps; t; t = t.nextDep) Vi(t);
      this.deps = this.depsTail = void 0, $s(this), this.onStop && this.onStop(), this.flags &= -2;
    }
  }
  trigger() {
    this.flags & 64 ? Lo.add(this) : this.scheduler ? this.scheduler() : this.runIfDirty();
  }
  runIfDirty() {
    ri(this) && this.run();
  }
  get dirty() {
    return ri(this);
  }
}
let bc = 0, Ir;
function Ni() {
  bc++;
}
function Ii() {
  if (--bc > 0) return;
  let e;
  for (; Ir; ) {
    let t = Ir;
    for (Ir = void 0; t; ) {
      const n = t.nextEffect;
      if (t.nextEffect = void 0, t.flags &= -9, t.flags & 1) try {
        t.trigger();
      } catch (a) {
        e || (e = a);
      }
      t = n;
    }
  }
  if (e) throw e;
}
function _c(e) {
  for (let t = e.deps; t; t = t.nextDep) t.version = -1, t.prevActiveLink = t.dep.activeLink, t.dep.activeLink = t;
}
function xc(e) {
  let t, n = e.depsTail;
  for (let a = n; a; a = a.prevDep) a.version === -1 ? (a === n && (n = a.prevDep), Vi(a), Mf(a)) : t = a, a.dep.activeLink = a.prevActiveLink, a.prevActiveLink = void 0;
  e.deps = t, e.depsTail = n;
}
function ri(e) {
  for (let t = e.deps; t; t = t.nextDep) if (t.dep.version !== t.version || t.dep.computed && kc(t.dep.computed) || t.dep.version !== t.version) return true;
  return !!e._dirty;
}
function kc(e) {
  if (e.flags & 4 && !(e.flags & 16) || (e.flags &= -17, e.globalVersion === Kr)) return;
  e.globalVersion = Kr;
  const t = e.dep;
  if (e.flags |= 2, t.version > 0 && !e.isSSR && !ri(e)) {
    e.flags &= -3;
    return;
  }
  const n = Qe, a = mn;
  Qe = e, mn = true;
  try {
    _c(e);
    const r = e.fn(e._value);
    (t.version === 0 || ma(r, e._value)) && (e._value = r, t.version++);
  } catch (r) {
    throw t.version++, r;
  } finally {
    Qe = n, mn = a, xc(e), e.flags &= -3;
  }
}
function Vi(e) {
  const { dep: t, prevSub: n, nextSub: a } = e;
  if (n && (n.nextSub = a, e.prevSub = void 0), a && (a.prevSub = n, e.nextSub = void 0), t.subs === e && (t.subs = n), !t.subs && t.computed) {
    t.computed.flags &= -5;
    for (let r = t.computed.deps; r; r = r.nextDep) Vi(r);
  }
}
function Mf(e) {
  const { prevDep: t, nextDep: n } = e;
  t && (t.nextDep = n, e.prevDep = void 0), n && (n.prevDep = t, e.nextDep = void 0);
}
let mn = true;
const Sc = [];
function ba() {
  Sc.push(mn), mn = false;
}
function _a() {
  const e = Sc.pop();
  mn = e === void 0 ? true : e;
}
function $s(e) {
  const { cleanup: t } = e;
  if (e.cleanup = void 0, t) {
    const n = Qe;
    Qe = void 0;
    try {
      t();
    } finally {
      Qe = n;
    }
  }
}
let Kr = 0;
class Xl {
  constructor(t) {
    this.computed = t, this.version = 0, this.activeLink = void 0, this.subs = void 0;
  }
  track(t) {
    if (!Qe || !mn || Qe === this.computed) return;
    let n = this.activeLink;
    if (n === void 0 || n.sub !== Qe) n = this.activeLink = { dep: this, sub: Qe, version: this.version, nextDep: void 0, prevDep: void 0, nextSub: void 0, prevSub: void 0, prevActiveLink: void 0 }, Qe.deps ? (n.prevDep = Qe.depsTail, Qe.depsTail.nextDep = n, Qe.depsTail = n) : Qe.deps = Qe.depsTail = n, Qe.flags & 4 && Cc(n);
    else if (n.version === -1 && (n.version = this.version, n.nextDep)) {
      const a = n.nextDep;
      a.prevDep = n.prevDep, n.prevDep && (n.prevDep.nextDep = a), n.prevDep = Qe.depsTail, n.nextDep = void 0, Qe.depsTail.nextDep = n, Qe.depsTail = n, Qe.deps === n && (Qe.deps = a);
    }
    return n;
  }
  trigger(t) {
    this.version++, Kr++, this.notify(t);
  }
  notify(t) {
    Ni();
    try {
      for (let n = this.subs; n; n = n.prevSub) n.sub.notify();
    } finally {
      Ii();
    }
  }
}
function Cc(e) {
  const t = e.dep.computed;
  if (t && !e.dep.subs) {
    t.flags |= 20;
    for (let a = t.deps; a; a = a.nextDep) Cc(a);
  }
  const n = e.dep.subs;
  n !== e && (e.prevSub = n, n && (n.nextSub = e)), e.dep.subs = e;
}
const Nl = /* @__PURE__ */ new WeakMap(), Ra = Symbol(""), li = Symbol(""), Zr = Symbol("");
function Ft(e, t, n) {
  if (mn && Qe) {
    let a = Nl.get(e);
    a || Nl.set(e, a = /* @__PURE__ */ new Map());
    let r = a.get(n);
    r || a.set(n, r = new Xl()), r.track();
  }
}
function Yn(e, t, n, a, r, l) {
  const o = Nl.get(e);
  if (!o) {
    Kr++;
    return;
  }
  const i = (s) => {
    s && s.trigger();
  };
  if (Ni(), t === "clear") o.forEach(i);
  else {
    const s = $e(e), c = s && Ri(n);
    if (s && n === "length") {
      const d = Number(a);
      o.forEach((u, p) => {
        (p === "length" || p === Zr || !wa(p) && p >= d) && i(u);
      });
    } else switch (n !== void 0 && i(o.get(n)), c && i(o.get(Zr)), t) {
      case "add":
        s ? c && i(o.get("length")) : (i(o.get(Ra)), nr(e) && i(o.get(li)));
        break;
      case "delete":
        s || (i(o.get(Ra)), nr(e) && i(o.get(li)));
        break;
      case "set":
        nr(e) && i(o.get(Ra));
        break;
    }
  }
  Ii();
}
function Tf(e, t) {
  var n;
  return (n = Nl.get(e)) == null ? void 0 : n.get(t);
}
function Za(e) {
  const t = Fe(e);
  return t === e ? t : (Ft(t, "iterate", Zr), on(e) ? t : t.map(It));
}
function Jl(e) {
  return Ft(e = Fe(e), "iterate", Zr), e;
}
const Af = { __proto__: null, [Symbol.iterator]() {
  return Oo(this, Symbol.iterator, It);
}, concat(...e) {
  return Za(this).concat(...e.map((t) => $e(t) ? Za(t) : t));
}, entries() {
  return Oo(this, "entries", (e) => (e[1] = It(e[1]), e));
}, every(e, t) {
  return Nn(this, "every", e, t, void 0, arguments);
}, filter(e, t) {
  return Nn(this, "filter", e, t, (n) => n.map(It), arguments);
}, find(e, t) {
  return Nn(this, "find", e, t, It, arguments);
}, findIndex(e, t) {
  return Nn(this, "findIndex", e, t, void 0, arguments);
}, findLast(e, t) {
  return Nn(this, "findLast", e, t, It, arguments);
}, findLastIndex(e, t) {
  return Nn(this, "findLastIndex", e, t, void 0, arguments);
}, forEach(e, t) {
  return Nn(this, "forEach", e, t, void 0, arguments);
}, includes(...e) {
  return Po(this, "includes", e);
}, indexOf(...e) {
  return Po(this, "indexOf", e);
}, join(e) {
  return Za(this).join(e);
}, lastIndexOf(...e) {
  return Po(this, "lastIndexOf", e);
}, map(e, t) {
  return Nn(this, "map", e, t, void 0, arguments);
}, pop() {
  return Sr(this, "pop");
}, push(...e) {
  return Sr(this, "push", e);
}, reduce(e, ...t) {
  return Rs(this, "reduce", e, t);
}, reduceRight(e, ...t) {
  return Rs(this, "reduceRight", e, t);
}, shift() {
  return Sr(this, "shift");
}, some(e, t) {
  return Nn(this, "some", e, t, void 0, arguments);
}, splice(...e) {
  return Sr(this, "splice", e);
}, toReversed() {
  return Za(this).toReversed();
}, toSorted(e) {
  return Za(this).toSorted(e);
}, toSpliced(...e) {
  return Za(this).toSpliced(...e);
}, unshift(...e) {
  return Sr(this, "unshift", e);
}, values() {
  return Oo(this, "values", It);
} };
function Oo(e, t, n) {
  const a = Jl(e), r = a[t]();
  return a !== e && !on(e) && (r._next = r.next, r.next = () => {
    const l = r._next();
    return l.value && (l.value = n(l.value)), l;
  }), r;
}
const Df = Array.prototype;
function Nn(e, t, n, a, r, l) {
  const o = Jl(e), i = o !== e && !on(e), s = o[t];
  if (s !== Df[t]) {
    const u = s.apply(e, l);
    return i ? It(u) : u;
  }
  let c = n;
  o !== e && (i ? c = function(u, p) {
    return n.call(this, It(u), p, e);
  } : n.length > 2 && (c = function(u, p) {
    return n.call(this, u, p, e);
  }));
  const d = s.call(o, c, a);
  return i && r ? r(d) : d;
}
function Rs(e, t, n, a) {
  const r = Jl(e);
  let l = n;
  return r !== e && (on(e) ? n.length > 3 && (l = function(o, i, s) {
    return n.call(this, o, i, s, e);
  }) : l = function(o, i, s) {
    return n.call(this, o, It(i), s, e);
  }), r[t](l, ...a);
}
function Po(e, t, n) {
  const a = Fe(e);
  Ft(a, "iterate", Zr);
  const r = a[t](...n);
  return (r === -1 || r === false) && Yi(n[0]) ? (n[0] = Fe(n[0]), a[t](...n)) : r;
}
function Sr(e, t, n = []) {
  ba(), Ni();
  const a = Fe(e)[t].apply(e, n);
  return Ii(), _a(), a;
}
const Lf = /* @__PURE__ */ Oi("__proto__,__v_isRef,__isVue"), Mc = new Set(Object.getOwnPropertyNames(Symbol).filter((e) => e !== "arguments" && e !== "caller").map((e) => Symbol[e]).filter(wa));
function Of(e) {
  wa(e) || (e = String(e));
  const t = Fe(this);
  return Ft(t, "has", e), t.hasOwnProperty(e);
}
class Tc {
  constructor(t = false, n = false) {
    this._isReadonly = t, this._isShallow = n;
  }
  get(t, n, a) {
    const r = this._isReadonly, l = this._isShallow;
    if (n === "__v_isReactive") return !r;
    if (n === "__v_isReadonly") return r;
    if (n === "__v_isShallow") return l;
    if (n === "__v_raw") return a === (r ? l ? zf : Oc : l ? Lc : Dc).get(t) || Object.getPrototypeOf(t) === Object.getPrototypeOf(a) ? t : void 0;
    const o = $e(t);
    if (!r) {
      let s;
      if (o && (s = Af[n])) return s;
      if (n === "hasOwnProperty") return Of;
    }
    const i = Reflect.get(t, n, at(t) ? t : a);
    return (wa(n) ? Mc.has(n) : Lf(n)) || (r || Ft(t, "get", n), l) ? i : at(i) ? o && Ri(n) ? i : i.value : et(i) ? r ? Pc(i) : un(i) : i;
  }
}
class Ac extends Tc {
  constructor(t = false) {
    super(false, t);
  }
  set(t, n, a, r) {
    let l = t[n];
    if (!this._isShallow) {
      const s = Fa(l);
      if (!on(a) && !Fa(a) && (l = Fe(l), a = Fe(a)), !$e(t) && at(l) && !at(a)) return s ? false : (l.value = a, true);
    }
    const o = $e(t) && Ri(n) ? Number(n) < t.length : We(t, n), i = Reflect.set(t, n, a, at(t) ? t : r);
    return t === Fe(r) && (o ? ma(a, l) && Yn(t, "set", n, a) : Yn(t, "add", n, a)), i;
  }
  deleteProperty(t, n) {
    const a = We(t, n);
    t[n];
    const r = Reflect.deleteProperty(t, n);
    return r && a && Yn(t, "delete", n, void 0), r;
  }
  has(t, n) {
    const a = Reflect.has(t, n);
    return (!wa(n) || !Mc.has(n)) && Ft(t, "has", n), a;
  }
  ownKeys(t) {
    return Ft(t, "iterate", $e(t) ? "length" : Ra), Reflect.ownKeys(t);
  }
}
class Pf extends Tc {
  constructor(t = false) {
    super(true, t);
  }
  set(t, n) {
    return true;
  }
  deleteProperty(t, n) {
    return true;
  }
}
const $f = new Ac(), Rf = new Pf(), Ef = new Ac(true), ji = (e) => e, eo = (e) => Reflect.getPrototypeOf(e);
function gl(e, t, n = false, a = false) {
  e = e.__v_raw;
  const r = Fe(e), l = Fe(t);
  n || (ma(t, l) && Ft(r, "get", t), Ft(r, "get", l));
  const { has: o } = eo(r), i = a ? ji : n ? zi : It;
  if (o.call(r, t)) return i(e.get(t));
  if (o.call(r, l)) return i(e.get(l));
  e !== r && e.get(t);
}
function wl(e, t = false) {
  const n = this.__v_raw, a = Fe(n), r = Fe(e);
  return t || (ma(e, r) && Ft(a, "has", e), Ft(a, "has", r)), e === r ? n.has(e) : n.has(e) || n.has(r);
}
function yl(e, t = false) {
  return e = e.__v_raw, !t && Ft(Fe(e), "iterate", Ra), Reflect.get(e, "size", e);
}
function Es(e, t = false) {
  !t && !on(e) && !Fa(e) && (e = Fe(e));
  const n = Fe(this);
  return eo(n).has.call(n, e) || (n.add(e), Yn(n, "add", e, e)), this;
}
function Ns(e, t, n = false) {
  !n && !on(t) && !Fa(t) && (t = Fe(t));
  const a = Fe(this), { has: r, get: l } = eo(a);
  let o = r.call(a, e);
  o || (e = Fe(e), o = r.call(a, e));
  const i = l.call(a, e);
  return a.set(e, t), o ? ma(t, i) && Yn(a, "set", e, t) : Yn(a, "add", e, t), this;
}
function Is(e) {
  const t = Fe(this), { has: n, get: a } = eo(t);
  let r = n.call(t, e);
  r || (e = Fe(e), r = n.call(t, e)), a && a.call(t, e);
  const l = t.delete(e);
  return r && Yn(t, "delete", e, void 0), l;
}
function Vs() {
  const e = Fe(this), t = e.size !== 0, n = e.clear();
  return t && Yn(e, "clear", void 0, void 0), n;
}
function bl(e, t) {
  return function(n, a) {
    const r = this, l = r.__v_raw, o = Fe(l), i = t ? ji : e ? zi : It;
    return !e && Ft(o, "iterate", Ra), l.forEach((s, c) => n.call(a, i(s), i(c), r));
  };
}
function _l(e, t, n) {
  return function(...a) {
    const r = this.__v_raw, l = Fe(r), o = nr(l), i = e === "entries" || e === Symbol.iterator && o, s = e === "keys" && o, c = r[e](...a), d = n ? ji : t ? zi : It;
    return !t && Ft(l, "iterate", s ? li : Ra), { next() {
      const { value: u, done: p } = c.next();
      return p ? { value: u, done: p } : { value: i ? [d(u[0]), d(u[1])] : d(u), done: p };
    }, [Symbol.iterator]() {
      return this;
    } };
  };
}
function Qn(e) {
  return function(...t) {
    return e === "delete" ? false : e === "clear" ? void 0 : this;
  };
}
function Nf() {
  const e = { get(r) {
    return gl(this, r);
  }, get size() {
    return yl(this);
  }, has: wl, add: Es, set: Ns, delete: Is, clear: Vs, forEach: bl(false, false) }, t = { get(r) {
    return gl(this, r, false, true);
  }, get size() {
    return yl(this);
  }, has: wl, add(r) {
    return Es.call(this, r, true);
  }, set(r, l) {
    return Ns.call(this, r, l, true);
  }, delete: Is, clear: Vs, forEach: bl(false, true) }, n = { get(r) {
    return gl(this, r, true);
  }, get size() {
    return yl(this, true);
  }, has(r) {
    return wl.call(this, r, true);
  }, add: Qn("add"), set: Qn("set"), delete: Qn("delete"), clear: Qn("clear"), forEach: bl(true, false) }, a = { get(r) {
    return gl(this, r, true, true);
  }, get size() {
    return yl(this, true);
  }, has(r) {
    return wl.call(this, r, true);
  }, add: Qn("add"), set: Qn("set"), delete: Qn("delete"), clear: Qn("clear"), forEach: bl(true, true) };
  return ["keys", "values", "entries", Symbol.iterator].forEach((r) => {
    e[r] = _l(r, false, false), n[r] = _l(r, true, false), t[r] = _l(r, false, true), a[r] = _l(r, true, true);
  }), [e, n, t, a];
}
const [If, Vf, jf, Bf] = Nf();
function Bi(e, t) {
  const n = t ? e ? Bf : jf : e ? Vf : If;
  return (a, r, l) => r === "__v_isReactive" ? !e : r === "__v_isReadonly" ? e : r === "__v_raw" ? a : Reflect.get(We(n, r) && r in a ? n : a, r, l);
}
const Ff = { get: Bi(false, false) }, Yf = { get: Bi(false, true) }, qf = { get: Bi(true, false) }, Dc = /* @__PURE__ */ new WeakMap(), Lc = /* @__PURE__ */ new WeakMap(), Oc = /* @__PURE__ */ new WeakMap(), zf = /* @__PURE__ */ new WeakMap();
function Hf(e) {
  switch (e) {
    case "Object":
    case "Array":
      return 1;
    case "Map":
    case "Set":
    case "WeakMap":
    case "WeakSet":
      return 2;
    default:
      return 0;
  }
}
function Kf(e) {
  return e.__v_skip || !Object.isExtensible(e) ? 0 : Hf(mf(e));
}
function un(e) {
  return Fa(e) ? e : Fi(e, false, $f, Ff, Dc);
}
function Zf(e) {
  return Fi(e, false, Ef, Yf, Lc);
}
function Pc(e) {
  return Fi(e, true, Rf, qf, Oc);
}
function Fi(e, t, n, a, r) {
  if (!et(e) || e.__v_raw && !(t && e.__v_isReactive)) return e;
  const l = r.get(e);
  if (l) return l;
  const o = Kf(e);
  if (o === 0) return e;
  const i = new Proxy(e, o === 2 ? a : n);
  return r.set(e, i), i;
}
function qn(e) {
  return Fa(e) ? qn(e.__v_raw) : !!(e && e.__v_isReactive);
}
function Fa(e) {
  return !!(e && e.__v_isReadonly);
}
function on(e) {
  return !!(e && e.__v_isShallow);
}
function Yi(e) {
  return e ? !!e.__v_raw : false;
}
function Fe(e) {
  const t = e && e.__v_raw;
  return t ? Fe(t) : e;
}
function qi(e) {
  return !We(e, "__v_skip") && Object.isExtensible(e) && dc(e, "__v_skip", true), e;
}
const It = (e) => et(e) ? un(e) : e, zi = (e) => et(e) ? Pc(e) : e;
function at(e) {
  return e ? e.__v_isRef === true : false;
}
function te(e) {
  return $c(e, false);
}
function Qa(e) {
  return $c(e, true);
}
function $c(e, t) {
  return at(e) ? e : new Wf(e, t);
}
class Wf {
  constructor(t, n) {
    this.dep = new Xl(), this.__v_isRef = true, this.__v_isShallow = false, this._rawValue = n ? t : Fe(t), this._value = n ? t : It(t), this.__v_isShallow = n;
  }
  get value() {
    return this.dep.track(), this._value;
  }
  set value(t) {
    const n = this._rawValue, a = this.__v_isShallow || on(t) || Fa(t);
    t = a ? t : Fe(t), ma(t, n) && (this._rawValue = t, this._value = a ? t : It(t), this.dep.trigger());
  }
}
function f(e) {
  return at(e) ? e.value : e;
}
const Uf = { get: (e, t, n) => t === "__v_raw" ? e : f(Reflect.get(e, t, n)), set: (e, t, n, a) => {
  const r = e[t];
  return at(r) && !at(n) ? (r.value = n, true) : Reflect.set(e, t, n, a);
} };
function Rc(e) {
  return qn(e) ? e : new Proxy(e, Uf);
}
class Gf {
  constructor(t) {
    this.__v_isRef = true, this._value = void 0;
    const n = this.dep = new Xl(), { get: a, set: r } = t(n.track.bind(n), n.trigger.bind(n));
    this._get = a, this._set = r;
  }
  get value() {
    return this._value = this._get();
  }
  set value(t) {
    this._set(t);
  }
}
function Qf(e) {
  return new Gf(e);
}
function Pt(e) {
  const t = $e(e) ? new Array(e.length) : {};
  for (const n in e) t[n] = Ec(e, n);
  return t;
}
class Xf {
  constructor(t, n, a) {
    this._object = t, this._key = n, this._defaultValue = a, this.__v_isRef = true, this._value = void 0;
  }
  get value() {
    const t = this._object[this._key];
    return this._value = t === void 0 ? this._defaultValue : t;
  }
  set value(t) {
    this._object[this._key] = t;
  }
  get dep() {
    return Tf(Fe(this._object), this._key);
  }
}
class Jf {
  constructor(t) {
    this._getter = t, this.__v_isRef = true, this.__v_isReadonly = true, this._value = void 0;
  }
  get value() {
    return this._value = this._getter();
  }
}
function ir(e, t, n) {
  return at(e) ? e : Ee(e) ? new Jf(e) : et(e) && arguments.length > 1 ? Ec(e, t, n) : te(e);
}
function Ec(e, t, n) {
  const a = e[t];
  return at(a) ? a : new Xf(e, t, n);
}
class e0 {
  constructor(t, n, a) {
    this.fn = t, this.setter = n, this._value = void 0, this.dep = new Xl(this), this.__v_isRef = true, this.deps = void 0, this.depsTail = void 0, this.flags = 16, this.globalVersion = Kr - 1, this.effect = this, this.__v_isReadonly = !n, this.isSSR = a;
  }
  notify() {
    this.flags |= 16, Qe !== this && this.dep.notify();
  }
  get value() {
    const t = this.dep.track();
    return kc(this), t && (t.version = this.dep.version), this._value;
  }
  set value(t) {
    this.setter && this.setter(t);
  }
}
function t0(e, t, n = false) {
  let a, r;
  return Ee(e) ? a = e : (a = e.get, r = e.set), new e0(a, r, n);
}
const xl = {}, Il = /* @__PURE__ */ new WeakMap();
let Oa;
function n0(e, t = false, n = Oa) {
  if (n) {
    let a = Il.get(n);
    a || Il.set(n, a = []), a.push(e);
  }
}
function a0(e, t, n = Xe) {
  const { immediate: a, deep: r, once: l, scheduler: o, augmentJob: i, call: s } = n, c = (g) => r ? g : on(g) || r === false || r === 0 ? Bn(g, 1) : Bn(g);
  let d, u, p, v, b = false, h = false;
  if (at(e) ? (u = () => e.value, b = on(e)) : qn(e) ? (u = () => c(e), b = true) : $e(e) ? (h = true, b = e.some((g) => qn(g) || on(g)), u = () => e.map((g) => {
    if (at(g)) return g.value;
    if (qn(g)) return c(g);
    if (Ee(g)) return s ? s(g, 2) : g();
  })) : Ee(e) ? t ? u = s ? () => s(e, 2) : e : u = () => {
    if (p) {
      ba();
      try {
        p();
      } finally {
        _a();
      }
    }
    const g = Oa;
    Oa = d;
    try {
      return s ? s(e, 3, [v]) : e(v);
    } finally {
      Oa = g;
    }
  } : u = Dn, t && r) {
    const g = u, R = r === true ? 1 / 0 : r;
    u = () => Bn(g(), R);
  }
  const N = Ei(), I = () => {
    d.stop(), N && $i(N.effects, d);
  };
  if (l) if (t) {
    const g = t;
    t = (...R) => {
      g(...R), I();
    };
  } else {
    const g = u;
    u = () => {
      g(), I();
    };
  }
  let x = h ? new Array(e.length).fill(xl) : xl;
  const _ = (g) => {
    if (!(!(d.flags & 1) || !d.dirty && !g)) if (t) {
      const R = d.run();
      if (r || b || (h ? R.some((M, C) => ma(M, x[C])) : ma(R, x))) {
        p && p();
        const M = Oa;
        Oa = d;
        try {
          const C = [R, x === xl ? void 0 : h && x[0] === xl ? [] : x, v];
          s ? s(t, 3, C) : t(...C), x = R;
        } finally {
          Oa = M;
        }
      }
    } else d.run();
  };
  return i && i(_), d = new yc(u), d.scheduler = o ? () => o(_, false) : _, v = (g) => n0(g, false, d), p = d.onStop = () => {
    const g = Il.get(d);
    if (g) {
      if (s) s(g, 4);
      else for (const R of g) R();
      Il.delete(d);
    }
  }, t ? a ? _(true) : x = d.run() : o ? o(_.bind(null, true), true) : d.run(), I.pause = d.pause.bind(d), I.resume = d.resume.bind(d), I.stop = I, I;
}
function Bn(e, t = 1 / 0, n) {
  if (t <= 0 || !et(e) || e.__v_skip || (n = n || /* @__PURE__ */ new Set(), n.has(e))) return e;
  if (n.add(e), t--, at(e)) Bn(e.value, t, n);
  else if ($e(e)) for (let a = 0; a < e.length; a++) Bn(e[a], t, n);
  else if (ic(e) || nr(e)) e.forEach((a) => {
    Bn(a, t, n);
  });
  else if (cc(e)) {
    for (const a in e) Bn(e[a], t, n);
    for (const a of Object.getOwnPropertySymbols(e)) Object.prototype.propertyIsEnumerable.call(e, a) && Bn(e[a], t, n);
  }
  return e;
}
/**
* @vue/runtime-core v3.5.4
* (c) 2018-present Yuxi (Evan) You and Vue contributors
* @license MIT
**/
function ll(e, t, n, a) {
  try {
    return a ? e(...a) : e();
  } catch (r) {
    to(r, t, n);
  }
}
function yn(e, t, n, a) {
  if (Ee(e)) {
    const r = ll(e, t, n, a);
    return r && sc(r) && r.catch((l) => {
      to(l, t, n);
    }), r;
  }
  if ($e(e)) {
    const r = [];
    for (let l = 0; l < e.length; l++) r.push(yn(e[l], t, n, a));
    return r;
  }
}
function to(e, t, n, a = true) {
  const r = t ? t.vnode : null, { errorHandler: l, throwUnhandledErrorInProduction: o } = t && t.appContext.config || Xe;
  if (t) {
    let i = t.parent;
    const s = t.proxy, c = `https://vuejs.org/error-reference/#runtime-${n}`;
    for (; i; ) {
      const d = i.ec;
      if (d) {
        for (let u = 0; u < d.length; u++) if (d[u](e, s, c) === false) return;
      }
      i = i.parent;
    }
    if (l) {
      ba(), ll(l, null, 10, [e, s, c]), _a();
      return;
    }
  }
  r0(e, n, r, a, o);
}
function r0(e, t, n, a = true, r = false) {
  if (r) throw e;
  console.error(e);
}
let Wr = false, oi = false;
const Kt = [];
let kn = 0;
const ar = [];
let aa = null, Xa = 0;
const Nc = Promise.resolve();
let Hi = null;
function bt(e) {
  const t = Hi || Nc;
  return e ? t.then(this ? e.bind(this) : e) : t;
}
function l0(e) {
  let t = Wr ? kn + 1 : 0, n = Kt.length;
  for (; t < n; ) {
    const a = t + n >>> 1, r = Kt[a], l = Ur(r);
    l < e || l === e && r.flags & 2 ? t = a + 1 : n = a;
  }
  return t;
}
function Ki(e) {
  if (!(e.flags & 1)) {
    const t = Ur(e), n = Kt[Kt.length - 1];
    !n || !(e.flags & 2) && t >= Ur(n) ? Kt.push(e) : Kt.splice(l0(t), 0, e), e.flags |= 1, Ic();
  }
}
function Ic() {
  !Wr && !oi && (oi = true, Hi = Nc.then(jc));
}
function o0(e) {
  $e(e) ? ar.push(...e) : aa && e.id === -1 ? aa.splice(Xa + 1, 0, e) : e.flags & 1 || (ar.push(e), e.flags |= 1), Ic();
}
function js(e, t, n = Wr ? kn + 1 : 0) {
  for (; n < Kt.length; n++) {
    const a = Kt[n];
    if (a && a.flags & 2) {
      if (e && a.id !== e.uid) continue;
      Kt.splice(n, 1), n--, a.flags & 4 && (a.flags &= -2), a(), a.flags &= -2;
    }
  }
}
function Vc(e) {
  if (ar.length) {
    const t = [...new Set(ar)].sort((n, a) => Ur(n) - Ur(a));
    if (ar.length = 0, aa) {
      aa.push(...t);
      return;
    }
    for (aa = t, Xa = 0; Xa < aa.length; Xa++) {
      const n = aa[Xa];
      n.flags & 4 && (n.flags &= -2), n.flags & 8 || n(), n.flags &= -2;
    }
    aa = null, Xa = 0;
  }
}
const Ur = (e) => e.id == null ? e.flags & 2 ? -1 : 1 / 0 : e.id;
function jc(e) {
  oi = false, Wr = true;
  try {
    for (kn = 0; kn < Kt.length; kn++) {
      const t = Kt[kn];
      t && !(t.flags & 8) && (t.flags & 4 && (t.flags &= -2), ll(t, t.i, t.i ? 15 : 14), t.flags &= -2);
    }
  } finally {
    for (; kn < Kt.length; kn++) {
      const t = Kt[kn];
      t && (t.flags &= -2);
    }
    kn = 0, Kt.length = 0, Vc(), Wr = false, Hi = null, (Kt.length || ar.length) && jc();
  }
}
let St = null, Bc = null;
function Vl(e) {
  const t = St;
  return St = e, Bc = e && e.type.__scopeId || null, t;
}
function Ie(e, t = St, n) {
  if (!t || e._n) return e;
  const a = (...r) => {
    a._d && Gs(-1);
    const l = Vl(t);
    let o;
    try {
      o = e(...r);
    } finally {
      Vl(l), a._d && Gs(1);
    }
    return o;
  };
  return a._n = true, a._c = true, a._d = true, a;
}
function zn(e, t) {
  if (St === null) return e;
  const n = uo(St), a = e.dirs || (e.dirs = []);
  for (let r = 0; r < t.length; r++) {
    let [l, o, i, s = Xe] = t[r];
    l && (Ee(l) && (l = { mounted: l, updated: l }), l.deep && Bn(o), a.push({ dir: l, instance: n, value: o, oldValue: void 0, arg: i, modifiers: s }));
  }
  return e;
}
function Ta(e, t, n, a) {
  const r = e.dirs, l = t && t.dirs;
  for (let o = 0; o < r.length; o++) {
    const i = r[o];
    l && (i.oldValue = l[o].value);
    let s = i.dir[a];
    s && (ba(), yn(s, n, 8, [e.el, i, e, t]), _a());
  }
}
const Fc = Symbol("_vte"), Yc = (e) => e.__isTeleport, Vr = (e) => e && (e.disabled || e.disabled === ""), i0 = (e) => e && (e.defer || e.defer === ""), Bs = (e) => typeof SVGElement < "u" && e instanceof SVGElement, Fs = (e) => typeof MathMLElement == "function" && e instanceof MathMLElement, ii = (e, t) => {
  const n = e && e.to;
  return rt(n) ? t ? t(n) : null : n;
}, s0 = { name: "Teleport", __isTeleport: true, process(e, t, n, a, r, l, o, i, s, c) {
  const { mc: d, pc: u, pbc: p, o: { insert: v, querySelector: b, createText: h, createComment: N } } = c, I = Vr(t.props);
  let { shapeFlag: x, children: _, dynamicChildren: g } = t;
  if (e == null) {
    const R = t.el = h(""), M = t.anchor = h("");
    v(R, n, a), v(M, n, a);
    const C = (P, $) => {
      x & 16 && d(_, P, $, r, l, o, i, s);
    }, Y = () => {
      const P = t.target = ii(t.props, b), $ = zc(P, t, h, v);
      P && (o !== "svg" && Bs(P) ? o = "svg" : o !== "mathml" && Fs(P) && (o = "mathml"), I || (C(P, $), Ll(t)));
    };
    I && (C(n, M), Ll(t)), i0(t.props) ? At(Y, l) : Y();
  } else {
    t.el = e.el, t.targetStart = e.targetStart;
    const R = t.anchor = e.anchor, M = t.target = e.target, C = t.targetAnchor = e.targetAnchor, Y = Vr(e.props), P = Y ? n : M, $ = Y ? R : C;
    if (o === "svg" || Bs(M) ? o = "svg" : (o === "mathml" || Fs(M)) && (o = "mathml"), g ? (p(e.dynamicChildren, g, P, r, l, o, i), Ui(e, t, true)) : s || u(e, t, P, $, r, l, o, i, false), I) Y ? t.props && e.props && t.props.to !== e.props.to && (t.props.to = e.props.to) : kl(t, n, R, c, 1);
    else if ((t.props && t.props.to) !== (e.props && e.props.to)) {
      const H = t.target = ii(t.props, b);
      H && kl(t, H, null, c, 0);
    } else Y && kl(t, M, C, c, 1);
    Ll(t);
  }
}, remove(e, t, n, { um: a, o: { remove: r } }, l) {
  const { shapeFlag: o, children: i, anchor: s, targetStart: c, targetAnchor: d, target: u, props: p } = e;
  if (u && (r(c), r(d)), l && r(s), o & 16) {
    const v = l || !Vr(p);
    for (let b = 0; b < i.length; b++) {
      const h = i[b];
      a(h, t, n, v, !!h.dynamicChildren);
    }
  }
}, move: kl, hydrate: u0 };
function kl(e, t, n, { o: { insert: a }, m: r }, l = 2) {
  l === 0 && a(e.targetAnchor, t, n);
  const { el: o, anchor: i, shapeFlag: s, children: c, props: d } = e, u = l === 2;
  if (u && a(o, t, n), (!u || Vr(d)) && s & 16) for (let p = 0; p < c.length; p++) r(c[p], t, n, 2);
  u && a(i, t, n);
}
function u0(e, t, n, a, r, l, { o: { nextSibling: o, parentNode: i, querySelector: s, insert: c, createText: d } }, u) {
  const p = t.target = ii(t.props, s);
  if (p) {
    const v = p._lpa || p.firstChild;
    if (t.shapeFlag & 16) if (Vr(t.props)) t.anchor = u(o(e), t, i(e), n, a, r, l), t.targetStart = v, t.targetAnchor = v && o(v);
    else {
      t.anchor = o(e);
      let b = v;
      for (; b; ) {
        if (b && b.nodeType === 8) {
          if (b.data === "teleport start anchor") t.targetStart = b;
          else if (b.data === "teleport anchor") {
            t.targetAnchor = b, p._lpa = t.targetAnchor && o(t.targetAnchor);
            break;
          }
        }
        b = o(b);
      }
      t.targetAnchor || zc(p, t, d, c), u(v && o(v), t, p, n, a, r, l);
    }
    Ll(t);
  }
  return t.anchor && o(t.anchor);
}
const qc = s0;
function Ll(e) {
  const t = e.ctx;
  if (t && t.ut) {
    let n = e.targetStart;
    for (; n && n !== e.targetAnchor; ) n.nodeType === 1 && n.setAttribute("data-v-owner", t.uid), n = n.nextSibling;
    t.ut();
  }
}
function zc(e, t, n, a) {
  const r = t.targetStart = n(""), l = t.targetAnchor = n("");
  return r[Fc] = l, e && (a(r, e), a(l, e)), l;
}
const ra = Symbol("_leaveCb"), Sl = Symbol("_enterCb");
function c0() {
  const e = { isMounted: false, isLeaving: false, isUnmounting: false, leavingVNodes: /* @__PURE__ */ new Map() };
  return ot(() => {
    e.isMounted = true;
  }), ro(() => {
    e.isUnmounting = true;
  }), e;
}
const rn = [Function, Array], Hc = { mode: String, appear: Boolean, persisted: Boolean, onBeforeEnter: rn, onEnter: rn, onAfterEnter: rn, onEnterCancelled: rn, onBeforeLeave: rn, onLeave: rn, onAfterLeave: rn, onLeaveCancelled: rn, onBeforeAppear: rn, onAppear: rn, onAfterAppear: rn, onAppearCancelled: rn }, Kc = (e) => {
  const t = e.subTree;
  return t.component ? Kc(t.component) : t;
}, d0 = { name: "BaseTransition", props: Hc, setup(e, { slots: t }) {
  const n = xa(), a = c0();
  return () => {
    const r = t.default && Uc(t.default(), true);
    if (!r || !r.length) return;
    const l = Zc(r), o = Fe(e), { mode: i } = o;
    if (a.isLeaving) return $o(l);
    const s = Ys(l);
    if (!s) return $o(l);
    let c = si(s, o, a, n, (p) => c = p);
    s.type !== Vt && sr(s, c);
    const d = n.subTree, u = d && Ys(d);
    if (u && u.type !== Vt && !ia(s, u) && Kc(n).type !== Vt) {
      const p = si(u, o, a, n);
      if (sr(u, p), i === "out-in" && s.type !== Vt) return a.isLeaving = true, p.afterLeave = () => {
        a.isLeaving = false, n.job.flags & 8 || n.update(), delete p.afterLeave;
      }, $o(l);
      i === "in-out" && s.type !== Vt && (p.delayLeave = (v, b, h) => {
        const N = Wc(a, u);
        N[String(u.key)] = u, v[ra] = () => {
          b(), v[ra] = void 0, delete c.delayedLeave;
        }, c.delayedLeave = h;
      });
    }
    return l;
  };
} };
function Zc(e) {
  let t = e[0];
  if (e.length > 1) {
    for (const n of e) if (n.type !== Vt) {
      t = n;
      break;
    }
  }
  return t;
}
const p0 = d0;
function Wc(e, t) {
  const { leavingVNodes: n } = e;
  let a = n.get(t.type);
  return a || (a = /* @__PURE__ */ Object.create(null), n.set(t.type, a)), a;
}
function si(e, t, n, a, r) {
  const { appear: l, mode: o, persisted: i = false, onBeforeEnter: s, onEnter: c, onAfterEnter: d, onEnterCancelled: u, onBeforeLeave: p, onLeave: v, onAfterLeave: b, onLeaveCancelled: h, onBeforeAppear: N, onAppear: I, onAfterAppear: x, onAppearCancelled: _ } = t, g = String(e.key), R = Wc(n, e), M = (P, $) => {
    P && yn(P, a, 9, $);
  }, C = (P, $) => {
    const H = $[1];
    M(P, $), $e(P) ? P.every((z) => z.length <= 1) && H() : P.length <= 1 && H();
  }, Y = { mode: o, persisted: i, beforeEnter(P) {
    let $ = s;
    if (!n.isMounted) if (l) $ = N || s;
    else return;
    P[ra] && P[ra](true);
    const H = R[g];
    H && ia(e, H) && H.el[ra] && H.el[ra](), M($, [P]);
  }, enter(P) {
    let $ = c, H = d, z = u;
    if (!n.isMounted) if (l) $ = I || c, H = x || d, z = _ || u;
    else return;
    let se = false;
    const j = P[Sl] = (y) => {
      se || (se = true, y ? M(z, [P]) : M(H, [P]), Y.delayedLeave && Y.delayedLeave(), P[Sl] = void 0);
    };
    $ ? C($, [P, j]) : j();
  }, leave(P, $) {
    const H = String(e.key);
    if (P[Sl] && P[Sl](true), n.isUnmounting) return $();
    M(p, [P]);
    let z = false;
    const se = P[ra] = (j) => {
      z || (z = true, $(), j ? M(h, [P]) : M(b, [P]), P[ra] = void 0, R[H] === e && delete R[H]);
    };
    R[H] = e, v ? C(v, [P, se]) : se();
  }, clone(P) {
    const $ = si(P, t, n, a, r);
    return r && r($), $;
  } };
  return Y;
}
function $o(e) {
  if (no(e)) return e = Zn(e), e.children = null, e;
}
function Ys(e) {
  if (!no(e)) return Yc(e.type) && e.children ? Zc(e.children) : e;
  const { shapeFlag: t, children: n } = e;
  if (n) {
    if (t & 16) return n[0];
    if (t & 32 && Ee(n.default)) return n.default();
  }
}
function sr(e, t) {
  e.shapeFlag & 6 && e.component ? (e.transition = t, sr(e.component.subTree, t)) : e.shapeFlag & 128 ? (e.ssContent.transition = t.clone(e.ssContent), e.ssFallback.transition = t.clone(e.ssFallback)) : e.transition = t;
}
function Uc(e, t = false, n) {
  let a = [], r = 0;
  for (let l = 0; l < e.length; l++) {
    let o = e[l];
    const i = n == null ? o.key : String(n) + String(o.key != null ? o.key : l);
    o.type === Ce ? (o.patchFlag & 128 && r++, a = a.concat(Uc(o.children, t, i))) : (t || o.type !== Vt) && a.push(i != null ? Zn(o, { key: i }) : o);
  }
  if (r > 1) for (let l = 0; l < a.length; l++) a[l].patchFlag = -2;
  return a;
}
/*! #__NO_SIDE_EFFECTS__ */
// @__NO_SIDE_EFFECTS__
function Rt(e, t) {
  return Ee(e) ? Ct({ name: e.name }, t, { setup: e }) : e;
}
function Gc(e) {
  e.ids = [e.ids[0] + e.ids[2]++ + "-", 0, 0];
}
function ui(e, t, n, a, r = false) {
  if ($e(e)) {
    e.forEach((b, h) => ui(b, t && ($e(t) ? t[h] : t), n, a, r));
    return;
  }
  if (Ea(a) && !r) return;
  const l = a.shapeFlag & 4 ? uo(a.component) : a.el, o = r ? null : l, { i, r: s } = e, c = t && t.r, d = i.refs === Xe ? i.refs = {} : i.refs, u = i.setupState, p = Fe(u), v = u === Xe ? () => false : (b) => We(p, b);
  if (c != null && c !== s && (rt(c) ? (d[c] = null, v(c) && (u[c] = null)) : at(c) && (c.value = null)), Ee(s)) ll(s, i, 12, [o, d]);
  else {
    const b = rt(s), h = at(s);
    if (b || h) {
      const N = () => {
        if (e.f) {
          const I = b ? v(s) ? u[s] : d[s] : s.value;
          r ? $e(I) && $i(I, l) : $e(I) ? I.includes(l) || I.push(l) : b ? (d[s] = [l], v(s) && (u[s] = d[s])) : (s.value = [l], e.k && (d[e.k] = s.value));
        } else b ? (d[s] = o, v(s) && (u[s] = o)) : h && (s.value = o, e.k && (d[e.k] = o));
      };
      o ? (N.id = -1, At(N, n)) : N();
    }
  }
}
const Ea = (e) => !!e.type.__asyncLoader, no = (e) => e.type.__isKeepAlive, f0 = { name: "KeepAlive", __isKeepAlive: true, props: { include: [String, RegExp, Array], exclude: [String, RegExp, Array], max: [String, Number] }, setup(e, { slots: t }) {
  const n = xa(), a = n.ctx;
  if (!a.renderer) return () => {
    const x = t.default && t.default();
    return x && x.length === 1 ? x[0] : x;
  };
  const r = /* @__PURE__ */ new Map(), l = /* @__PURE__ */ new Set();
  let o = null;
  const i = n.suspense, { renderer: { p: s, m: c, um: d, o: { createElement: u } } } = a, p = u("div");
  a.activate = (x, _, g, R, M) => {
    const C = x.component;
    c(x, _, g, 0, i), s(C.vnode, x, _, g, C, i, R, x.slotScopeIds, M), At(() => {
      C.isDeactivated = false, C.a && Nr(C.a);
      const Y = x.props && x.props.onVnodeMounted;
      Y && ln(Y, C.parent, x);
    }, i);
  }, a.deactivate = (x) => {
    const _ = x.component;
    Bl(_.m), Bl(_.a), c(x, p, null, 1, i), At(() => {
      _.da && Nr(_.da);
      const g = x.props && x.props.onVnodeUnmounted;
      g && ln(g, _.parent, x), _.isDeactivated = true;
    }, i);
  };
  function v(x) {
    Ro(x), d(x, n, i, true);
  }
  function b(x) {
    r.forEach((_, g) => {
      const R = mi(_.type);
      R && !x(R) && h(g);
    });
  }
  function h(x) {
    const _ = r.get(x);
    _ && (!o || !ia(_, o)) ? v(_) : o && Ro(o), r.delete(x), l.delete(x);
  }
  He(() => [e.include, e.exclude], ([x, _]) => {
    x && b((g) => Pr(x, g)), _ && b((g) => !Pr(_, g));
  }, { flush: "post", deep: true });
  let N = null;
  const I = () => {
    N != null && (Fl(n.subTree.type) ? At(() => {
      r.set(N, Cl(n.subTree));
    }, n.subTree.suspense) : r.set(N, Cl(n.subTree)));
  };
  return ot(I), Jc(I), ro(() => {
    r.forEach((x) => {
      const { subTree: _, suspense: g } = n, R = Cl(_);
      if (x.type === R.type && x.key === R.key) {
        Ro(R);
        const M = R.component.da;
        M && At(M, g);
        return;
      }
      v(x);
    });
  }), () => {
    if (N = null, !t.default) return o = null;
    const x = t.default(), _ = x[0];
    if (x.length > 1) return o = null, x;
    if (!Qr(_) || !(_.shapeFlag & 4) && !(_.shapeFlag & 128)) return o = null, _;
    let g = Cl(_);
    if (g.type === Vt) return o = null, g;
    const R = g.type, M = mi(Ea(g) ? g.type.__asyncResolved || {} : R), { include: C, exclude: Y, max: P } = e;
    if (C && (!M || !Pr(C, M)) || Y && M && Pr(Y, M)) return g.shapeFlag &= -257, o = g, _;
    const $ = g.key == null ? R : g.key, H = r.get($);
    return g.el && (g = Zn(g), _.shapeFlag & 128 && (_.ssContent = g)), N = $, H ? (g.el = H.el, g.component = H.component, g.transition && sr(g, g.transition), g.shapeFlag |= 512, l.delete($), l.add($)) : (l.add($), P && l.size > parseInt(P, 10) && h(l.values().next().value)), g.shapeFlag |= 256, o = g, Fl(_.type) ? _ : g;
  };
} }, v0 = f0;
function Pr(e, t) {
  return $e(e) ? e.some((n) => Pr(n, t)) : rt(e) ? e.split(",").includes(t) : vf(e) ? (e.lastIndex = 0, e.test(t)) : false;
}
function m0(e, t) {
  Qc(e, "a", t);
}
function h0(e, t) {
  Qc(e, "da", t);
}
function Qc(e, t, n = Dt) {
  const a = e.__wdc || (e.__wdc = () => {
    let r = n;
    for (; r; ) {
      if (r.isDeactivated) return;
      r = r.parent;
    }
    return e();
  });
  if (ao(t, a, n), n) {
    let r = n.parent;
    for (; r && r.parent; ) no(r.parent.vnode) && g0(a, t, n, r), r = r.parent;
  }
}
function g0(e, t, n, a) {
  const r = ao(t, e, a, true);
  br(() => {
    $i(a[t], r);
  }, n);
}
function Ro(e) {
  e.shapeFlag &= -257, e.shapeFlag &= -513;
}
function Cl(e) {
  return e.shapeFlag & 128 ? e.ssContent : e;
}
function ao(e, t, n = Dt, a = false) {
  if (n) {
    const r = n[e] || (n[e] = []), l = t.__weh || (t.__weh = (...o) => {
      ba();
      const i = il(n), s = yn(t, n, e, o);
      return i(), _a(), s;
    });
    return a ? r.unshift(l) : r.push(l), l;
  }
}
const Gn = (e) => (t, n = Dt) => {
  (!so || e === "sp") && ao(e, (...a) => t(...a), n);
}, yr = Gn("bm"), ot = Gn("m"), Xc = Gn("bu"), Jc = Gn("u"), ro = Gn("bum"), br = Gn("um"), w0 = Gn("sp"), y0 = Gn("rtg"), b0 = Gn("rtc");
function _0(e, t = Dt) {
  ao("ec", e, t);
}
const x0 = "components", ed = Symbol.for("v-ndc");
function ol(e) {
  return rt(e) ? k0(x0, e, false) || e : e || ed;
}
function k0(e, t, n = true, a = false) {
  const r = St || Dt;
  if (r) {
    const l = r.type;
    {
      const i = mi(l, false);
      if (i && (i === t || i === wn(t) || i === Ql(wn(t)))) return l;
    }
    const o = qs(r[e] || l[e], t) || qs(r.appContext[e], t);
    return !o && a ? l : o;
  }
}
function qs(e, t) {
  return e && (e[t] || e[wn(t)] || e[Ql(wn(t))]);
}
function Ve(e, t, n, a) {
  let r;
  const l = n, o = $e(e);
  if (o || rt(e)) {
    const i = o && qn(e);
    let s = false;
    i && (s = !on(e), e = Jl(e)), r = new Array(e.length);
    for (let c = 0, d = e.length; c < d; c++) r[c] = t(s ? It(e[c]) : e[c], c, void 0, l);
  } else if (typeof e == "number") {
    r = new Array(e);
    for (let i = 0; i < e; i++) r[i] = t(i + 1, i, void 0, l);
  } else if (et(e)) if (e[Symbol.iterator]) r = Array.from(e, (i, s) => t(i, s, void 0, l));
  else {
    const i = Object.keys(e);
    r = new Array(i.length);
    for (let s = 0, c = i.length; s < c; s++) {
      const d = i[s];
      r[s] = t(e[d], d, s, l);
    }
  }
  else r = [];
  return r;
}
function jt(e, t) {
  for (let n = 0; n < t.length; n++) {
    const a = t[n];
    if ($e(a)) for (let r = 0; r < a.length; r++) e[a[r].name] = a[r].fn;
    else a && (e[a.name] = a.key ? (...r) => {
      const l = a.fn(...r);
      return l && (l.key = a.key), l;
    } : a.fn);
  }
  return e;
}
function ye(e, t, n = {}, a, r) {
  if (St.ce || St.parent && Ea(St.parent) && St.parent.ce) return t !== "default" && (n.name = t), T(), Pe(Ce, null, [Ne("slot", n, a && a())], 64);
  let l = e[t];
  l && l._c && (l._d = false), T();
  const o = l && td(l(n)), i = Pe(Ce, { key: (n.key || o && o.key || `_${t}`) + (!o && a ? "_fb" : "") }, o || (a ? a() : []), o && e._ === 1 ? 64 : -2);
  return i.scopeId && (i.slotScopeIds = [i.scopeId + "-s"]), l && l._c && (l._d = true), i;
}
function td(e) {
  return e.some((t) => Qr(t) ? !(t.type === Vt || t.type === Ce && !td(t.children)) : true) ? e : null;
}
const ci = (e) => e ? yd(e) ? uo(e) : ci(e.parent) : null, jr = Ct(/* @__PURE__ */ Object.create(null), { $: (e) => e, $el: (e) => e.vnode.el, $data: (e) => e.data, $props: (e) => e.props, $attrs: (e) => e.attrs, $slots: (e) => e.slots, $refs: (e) => e.refs, $parent: (e) => ci(e.parent), $root: (e) => ci(e.root), $host: (e) => e.ce, $emit: (e) => e.emit, $options: (e) => Zi(e), $forceUpdate: (e) => e.f || (e.f = () => {
  Ki(e.update);
}), $nextTick: (e) => e.n || (e.n = bt.bind(e.proxy)), $watch: (e) => K0.bind(e) }), Eo = (e, t) => e !== Xe && !e.__isScriptSetup && We(e, t), S0 = { get({ _: e }, t) {
  if (t === "__v_skip") return true;
  const { ctx: n, setupState: a, data: r, props: l, accessCache: o, type: i, appContext: s } = e;
  let c;
  if (t[0] !== "$") {
    const v = o[t];
    if (v !== void 0) switch (v) {
      case 1:
        return a[t];
      case 2:
        return r[t];
      case 4:
        return n[t];
      case 3:
        return l[t];
    }
    else {
      if (Eo(a, t)) return o[t] = 1, a[t];
      if (r !== Xe && We(r, t)) return o[t] = 2, r[t];
      if ((c = e.propsOptions[0]) && We(c, t)) return o[t] = 3, l[t];
      if (n !== Xe && We(n, t)) return o[t] = 4, n[t];
      di && (o[t] = 0);
    }
  }
  const d = jr[t];
  let u, p;
  if (d) return t === "$attrs" && Ft(e.attrs, "get", ""), d(e);
  if ((u = i.__cssModules) && (u = u[t])) return u;
  if (n !== Xe && We(n, t)) return o[t] = 4, n[t];
  if (p = s.config.globalProperties, We(p, t)) return p[t];
}, set({ _: e }, t, n) {
  const { data: a, setupState: r, ctx: l } = e;
  return Eo(r, t) ? (r[t] = n, true) : a !== Xe && We(a, t) ? (a[t] = n, true) : We(e.props, t) || t[0] === "$" && t.slice(1) in e ? false : (l[t] = n, true);
}, has({ _: { data: e, setupState: t, accessCache: n, ctx: a, appContext: r, propsOptions: l } }, o) {
  let i;
  return !!n[o] || e !== Xe && We(e, o) || Eo(t, o) || (i = l[0]) && We(i, o) || We(a, o) || We(jr, o) || We(r.config.globalProperties, o);
}, defineProperty(e, t, n) {
  return n.get != null ? e._.accessCache[t] = 0 : We(n, "value") && this.set(e, t, n.value, null), Reflect.defineProperty(e, t, n);
} };
function za() {
  return C0().slots;
}
function C0() {
  const e = xa();
  return e.setupContext || (e.setupContext = _d(e));
}
function zs(e) {
  return $e(e) ? e.reduce((t, n) => (t[n] = null, t), {}) : e;
}
let di = true;
function M0(e) {
  const t = Zi(e), n = e.proxy, a = e.ctx;
  di = false, t.beforeCreate && Hs(t.beforeCreate, e, "bc");
  const { data: r, computed: l, methods: o, watch: i, provide: s, inject: c, created: d, beforeMount: u, mounted: p, beforeUpdate: v, updated: b, activated: h, deactivated: N, beforeDestroy: I, beforeUnmount: x, destroyed: _, unmounted: g, render: R, renderTracked: M, renderTriggered: C, errorCaptured: Y, serverPrefetch: P, expose: $, inheritAttrs: H, components: z, directives: se, filters: j } = t;
  if (c && T0(c, a, null), o) for (const V in o) {
    const w = o[V];
    Ee(w) && (a[V] = w.bind(n));
  }
  if (r) {
    const V = r.call(n, n);
    et(V) && (e.data = un(V));
  }
  if (di = true, l) for (const V in l) {
    const w = l[V], ae = Ee(w) ? w.bind(n, n) : Ee(w.get) ? w.get.bind(n, n) : Dn, ce = !Ee(w) && Ee(w.set) ? w.set.bind(n) : Dn, ve = J({ get: ae, set: ce });
    Object.defineProperty(a, V, { enumerable: true, configurable: true, get: () => ve.value, set: (_e) => ve.value = _e });
  }
  if (i) for (const V in i) nd(i[V], a, n, V);
  if (s) {
    const V = Ee(s) ? s.call(n) : s;
    Reflect.ownKeys(V).forEach((w) => {
      lo(w, V[w]);
    });
  }
  d && Hs(d, e, "c");
  function y(V, w) {
    $e(w) ? w.forEach((ae) => V(ae.bind(n))) : w && V(w.bind(n));
  }
  if (y(yr, u), y(ot, p), y(Xc, v), y(Jc, b), y(m0, h), y(h0, N), y(_0, Y), y(b0, M), y(y0, C), y(ro, x), y(br, g), y(w0, P), $e($)) if ($.length) {
    const V = e.exposed || (e.exposed = {});
    $.forEach((w) => {
      Object.defineProperty(V, w, { get: () => n[w], set: (ae) => n[w] = ae });
    });
  } else e.exposed || (e.exposed = {});
  R && e.render === Dn && (e.render = R), H != null && (e.inheritAttrs = H), z && (e.components = z), se && (e.directives = se), P && Gc(e);
}
function T0(e, t, n = Dn) {
  $e(e) && (e = pi(e));
  for (const a in e) {
    const r = e[a];
    let l;
    et(r) ? "default" in r ? l = Hn(r.from || a, r.default, true) : l = Hn(r.from || a) : l = Hn(r), at(l) ? Object.defineProperty(t, a, { enumerable: true, configurable: true, get: () => l.value, set: (o) => l.value = o }) : t[a] = l;
  }
}
function Hs(e, t, n) {
  yn($e(e) ? e.map((a) => a.bind(t.proxy)) : e.bind(t.proxy), t, n);
}
function nd(e, t, n, a) {
  let r = a.includes(".") ? md(n, a) : () => n[a];
  if (rt(e)) {
    const l = t[e];
    Ee(l) && He(r, l);
  } else if (Ee(e)) He(r, e.bind(n));
  else if (et(e)) if ($e(e)) e.forEach((l) => nd(l, t, n, a));
  else {
    const l = Ee(e.handler) ? e.handler.bind(n) : t[e.handler];
    Ee(l) && He(r, l, e);
  }
}
function Zi(e) {
  const t = e.type, { mixins: n, extends: a } = t, { mixins: r, optionsCache: l, config: { optionMergeStrategies: o } } = e.appContext, i = l.get(t);
  let s;
  return i ? s = i : !r.length && !n && !a ? s = t : (s = {}, r.length && r.forEach((c) => jl(s, c, o, true)), jl(s, t, o)), et(t) && l.set(t, s), s;
}
function jl(e, t, n, a = false) {
  const { mixins: r, extends: l } = t;
  l && jl(e, l, n, true), r && r.forEach((o) => jl(e, o, n, true));
  for (const o in t) if (!(a && o === "expose")) {
    const i = A0[o] || n && n[o];
    e[o] = i ? i(e[o], t[o]) : t[o];
  }
  return e;
}
const A0 = { data: Ks, props: Zs, emits: Zs, methods: $r, computed: $r, beforeCreate: qt, created: qt, beforeMount: qt, mounted: qt, beforeUpdate: qt, updated: qt, beforeDestroy: qt, beforeUnmount: qt, destroyed: qt, unmounted: qt, activated: qt, deactivated: qt, errorCaptured: qt, serverPrefetch: qt, components: $r, directives: $r, watch: L0, provide: Ks, inject: D0 };
function Ks(e, t) {
  return t ? e ? function() {
    return Ct(Ee(e) ? e.call(this, this) : e, Ee(t) ? t.call(this, this) : t);
  } : t : e;
}
function D0(e, t) {
  return $r(pi(e), pi(t));
}
function pi(e) {
  if ($e(e)) {
    const t = {};
    for (let n = 0; n < e.length; n++) t[e[n]] = e[n];
    return t;
  }
  return e;
}
function qt(e, t) {
  return e ? [...new Set([].concat(e, t))] : t;
}
function $r(e, t) {
  return e ? Ct(/* @__PURE__ */ Object.create(null), e, t) : t;
}
function Zs(e, t) {
  return e ? $e(e) && $e(t) ? [.../* @__PURE__ */ new Set([...e, ...t])] : Ct(/* @__PURE__ */ Object.create(null), zs(e), zs(t ?? {})) : t;
}
function L0(e, t) {
  if (!e) return t;
  if (!t) return e;
  const n = Ct(/* @__PURE__ */ Object.create(null), e);
  for (const a in t) n[a] = qt(e[a], t[a]);
  return n;
}
function ad() {
  return { app: null, config: { isNativeTag: pf, performance: false, globalProperties: {}, optionMergeStrategies: {}, errorHandler: void 0, warnHandler: void 0, compilerOptions: {} }, mixins: [], components: {}, directives: {}, provides: /* @__PURE__ */ Object.create(null), optionsCache: /* @__PURE__ */ new WeakMap(), propsCache: /* @__PURE__ */ new WeakMap(), emitsCache: /* @__PURE__ */ new WeakMap() };
}
let O0 = 0;
function P0(e, t) {
  return function(n, a = null) {
    Ee(n) || (n = Ct({}, n)), a != null && !et(a) && (a = null);
    const r = ad(), l = /* @__PURE__ */ new WeakSet(), o = [];
    let i = false;
    const s = r.app = { _uid: O0++, _component: n, _props: a, _container: null, _context: r, _instance: null, version: cv, get config() {
      return r.config;
    }, set config(c) {
    }, use(c, ...d) {
      return l.has(c) || (c && Ee(c.install) ? (l.add(c), c.install(s, ...d)) : Ee(c) && (l.add(c), c(s, ...d))), s;
    }, mixin(c) {
      return r.mixins.includes(c) || r.mixins.push(c), s;
    }, component(c, d) {
      return d ? (r.components[c] = d, s) : r.components[c];
    }, directive(c, d) {
      return d ? (r.directives[c] = d, s) : r.directives[c];
    }, mount(c, d, u) {
      if (!i) {
        const p = s._ceVNode || Ne(n, a);
        return p.appContext = r, u === true ? u = "svg" : u === false && (u = void 0), d && t ? t(p, c) : e(p, c, u), i = true, s._container = c, c.__vue_app__ = s, uo(p.component);
      }
    }, onUnmount(c) {
      o.push(c);
    }, unmount() {
      i && (yn(o, s._instance, 16), e(null, s._container), delete s._container.__vue_app__);
    }, provide(c, d) {
      return r.provides[c] = d, s;
    }, runWithContext(c) {
      const d = Na;
      Na = s;
      try {
        return c();
      } finally {
        Na = d;
      }
    } };
    return s;
  };
}
let Na = null;
function lo(e, t) {
  if (Dt) {
    let n = Dt.provides;
    const a = Dt.parent && Dt.parent.provides;
    a === n && (n = Dt.provides = Object.create(a)), n[e] = t;
  }
}
function Hn(e, t, n = false) {
  const a = Dt || St;
  if (a || Na) {
    const r = Na ? Na._context.provides : a ? a.parent == null ? a.vnode.appContext && a.vnode.appContext.provides : a.parent.provides : void 0;
    if (r && e in r) return r[e];
    if (arguments.length > 1) return n && Ee(t) ? t.call(a && a.proxy) : t;
  }
}
function $0() {
  return !!(Dt || St || Na);
}
const rd = {}, ld = () => Object.create(rd), od = (e) => Object.getPrototypeOf(e) === rd;
function R0(e, t, n, a = false) {
  const r = {}, l = ld();
  e.propsDefaults = /* @__PURE__ */ Object.create(null), id(e, t, r, l);
  for (const o in e.propsOptions[0]) o in r || (r[o] = void 0);
  n ? e.props = a ? r : Zf(r) : e.type.props ? e.props = r : e.props = l, e.attrs = l;
}
function E0(e, t, n, a) {
  const { props: r, attrs: l, vnode: { patchFlag: o } } = e, i = Fe(r), [s] = e.propsOptions;
  let c = false;
  if ((a || o > 0) && !(o & 16)) {
    if (o & 8) {
      const d = e.vnode.dynamicProps;
      for (let u = 0; u < d.length; u++) {
        let p = d[u];
        if (oo(e.emitsOptions, p)) continue;
        const v = t[p];
        if (s) if (We(l, p)) v !== l[p] && (l[p] = v, c = true);
        else {
          const b = wn(p);
          r[b] = fi(s, i, b, v, e, false);
        }
        else v !== l[p] && (l[p] = v, c = true);
      }
    }
  } else {
    id(e, t, r, l) && (c = true);
    let d;
    for (const u in i) (!t || !We(t, u) && ((d = ya(u)) === u || !We(t, d))) && (s ? n && (n[u] !== void 0 || n[d] !== void 0) && (r[u] = fi(s, i, u, void 0, e, true)) : delete r[u]);
    if (l !== i) for (const u in l) (!t || !We(t, u)) && (delete l[u], c = true);
  }
  c && Yn(e.attrs, "set", "");
}
function id(e, t, n, a) {
  const [r, l] = e.propsOptions;
  let o = false, i;
  if (t) for (let s in t) {
    if (Er(s)) continue;
    const c = t[s];
    let d;
    r && We(r, d = wn(s)) ? !l || !l.includes(d) ? n[d] = c : (i || (i = {}))[d] = c : oo(e.emitsOptions, s) || (!(s in a) || c !== a[s]) && (a[s] = c, o = true);
  }
  if (l) {
    const s = Fe(n), c = i || Xe;
    for (let d = 0; d < l.length; d++) {
      const u = l[d];
      n[u] = fi(r, s, u, c[u], e, !We(c, u));
    }
  }
  return o;
}
function fi(e, t, n, a, r, l) {
  const o = e[n];
  if (o != null) {
    const i = We(o, "default");
    if (i && a === void 0) {
      const s = o.default;
      if (o.type !== Function && !o.skipFactory && Ee(s)) {
        const { propsDefaults: c } = r;
        if (n in c) a = c[n];
        else {
          const d = il(r);
          a = c[n] = s.call(null, t), d();
        }
      } else a = s;
      r.ce && r.ce._setProp(n, a);
    }
    o[0] && (l && !i ? a = false : o[1] && (a === "" || a === ya(n)) && (a = true));
  }
  return a;
}
const N0 = /* @__PURE__ */ new WeakMap();
function sd(e, t, n = false) {
  const a = n ? N0 : t.propsCache, r = a.get(e);
  if (r) return r;
  const l = e.props, o = {}, i = [];
  let s = false;
  if (!Ee(e)) {
    const d = (u) => {
      s = true;
      const [p, v] = sd(u, t, true);
      Ct(o, p), v && i.push(...v);
    };
    !n && t.mixins.length && t.mixins.forEach(d), e.extends && d(e.extends), e.mixins && e.mixins.forEach(d);
  }
  if (!l && !s) return et(e) && a.set(e, tr), tr;
  if ($e(l)) for (let d = 0; d < l.length; d++) {
    const u = wn(l[d]);
    Ws(u) && (o[u] = Xe);
  }
  else if (l) for (const d in l) {
    const u = wn(d);
    if (Ws(u)) {
      const p = l[d], v = o[u] = $e(p) || Ee(p) ? { type: p } : Ct({}, p), b = v.type;
      let h = false, N = true;
      if ($e(b)) for (let I = 0; I < b.length; ++I) {
        const x = b[I], _ = Ee(x) && x.name;
        if (_ === "Boolean") {
          h = true;
          break;
        } else _ === "String" && (N = false);
      }
      else h = Ee(b) && b.name === "Boolean";
      v[0] = h, v[1] = N, (h || We(v, "default")) && i.push(u);
    }
  }
  const c = [o, i];
  return et(e) && a.set(e, c), c;
}
function Ws(e) {
  return e[0] !== "$" && !Er(e);
}
const ud = (e) => e[0] === "_" || e === "$stable", Wi = (e) => $e(e) ? e.map(Cn) : [Cn(e)], I0 = (e, t, n) => {
  if (t._n) return t;
  const a = Ie((...r) => Wi(t(...r)), n);
  return a._c = false, a;
}, cd = (e, t, n) => {
  const a = e._ctx;
  for (const r in e) {
    if (ud(r)) continue;
    const l = e[r];
    if (Ee(l)) t[r] = I0(r, l, a);
    else if (l != null) {
      const o = Wi(l);
      t[r] = () => o;
    }
  }
}, dd = (e, t) => {
  const n = Wi(t);
  e.slots.default = () => n;
}, pd = (e, t, n) => {
  for (const a in t) (n || a !== "_") && (e[a] = t[a]);
}, V0 = (e, t, n) => {
  const a = e.slots = ld();
  if (e.vnode.shapeFlag & 32) {
    const r = t._;
    r ? (pd(a, t, n), n && dc(a, "_", r, true)) : cd(t, a);
  } else t && dd(e, t);
}, j0 = (e, t, n) => {
  const { vnode: a, slots: r } = e;
  let l = true, o = Xe;
  if (a.shapeFlag & 32) {
    const i = t._;
    i ? n && i === 1 ? l = false : pd(r, t, n) : (l = !t.$stable, cd(t, r)), o = t;
  } else t && (dd(e, t), o = { default: 1 });
  if (l) for (const i in r) !ud(i) && o[i] == null && delete r[i];
}, At = J0;
function B0(e) {
  return F0(e);
}
function F0(e, t) {
  const n = pc();
  n.__VUE__ = true;
  const { insert: a, remove: r, patchProp: l, createElement: o, createText: i, createComment: s, setText: c, setElementText: d, parentNode: u, nextSibling: p, setScopeId: v = Dn, insertStaticContent: b } = e, h = (m, k, E, B = null, X = null, A = null, U = void 0, ee = null, le = !!k.dynamicChildren) => {
    if (m === k) return;
    m && !ia(m, k) && (B = D(m), q(m, X, A, true), m = null), k.patchFlag === -2 && (le = false, k.dynamicChildren = null);
    const { type: ue, ref: ie, shapeFlag: be } = k;
    switch (ue) {
      case io:
        N(m, k, E, B);
        break;
      case Vt:
        I(m, k, E, B);
        break;
      case Ol:
        m == null && x(k, E, B, U);
        break;
      case Ce:
        z(m, k, E, B, X, A, U, ee, le);
        break;
      default:
        be & 1 ? R(m, k, E, B, X, A, U, ee, le) : be & 6 ? se(m, k, E, B, X, A, U, ee, le) : (be & 64 || be & 128) && ue.process(m, k, E, B, X, A, U, ee, le, re);
    }
    ie != null && X && ui(ie, m && m.ref, A, k || m, !k);
  }, N = (m, k, E, B) => {
    if (m == null) a(k.el = i(k.children), E, B);
    else {
      const X = k.el = m.el;
      k.children !== m.children && c(X, k.children);
    }
  }, I = (m, k, E, B) => {
    m == null ? a(k.el = s(k.children || ""), E, B) : k.el = m.el;
  }, x = (m, k, E, B) => {
    [m.el, m.anchor] = b(m.children, k, E, B, m.el, m.anchor);
  }, _ = ({ el: m, anchor: k }, E, B) => {
    let X;
    for (; m && m !== k; ) X = p(m), a(m, E, B), m = X;
    a(k, E, B);
  }, g = ({ el: m, anchor: k }) => {
    let E;
    for (; m && m !== k; ) E = p(m), r(m), m = E;
    r(k);
  }, R = (m, k, E, B, X, A, U, ee, le) => {
    k.type === "svg" ? U = "svg" : k.type === "math" && (U = "mathml"), m == null ? M(k, E, B, X, A, U, ee, le) : P(m, k, X, A, U, ee, le);
  }, M = (m, k, E, B, X, A, U, ee) => {
    let le, ue;
    const { props: ie, shapeFlag: be, transition: xe, dirs: De } = m;
    if (le = m.el = o(m.type, A, ie && ie.is, ie), be & 8 ? d(le, m.children) : be & 16 && Y(m.children, le, null, B, X, No(m, A), U, ee), De && Ta(m, null, B, "created"), C(le, m, m.scopeId, U, B), ie) {
      for (const je in ie) je !== "value" && !Er(je) && l(le, je, null, ie[je], A, B);
      "value" in ie && l(le, "value", null, ie.value, A), (ue = ie.onVnodeBeforeMount) && ln(ue, B, m);
    }
    De && Ta(m, null, B, "beforeMount");
    const Le = Y0(X, xe);
    Le && xe.beforeEnter(le), a(le, k, E), ((ue = ie && ie.onVnodeMounted) || Le || De) && At(() => {
      ue && ln(ue, B, m), Le && xe.enter(le), De && Ta(m, null, B, "mounted");
    }, X);
  }, C = (m, k, E, B, X) => {
    if (E && v(m, E), B) for (let A = 0; A < B.length; A++) v(m, B[A]);
    if (X) {
      let A = X.subTree;
      if (k === A || Fl(A.type) && (A.ssContent === k || A.ssFallback === k)) {
        const U = X.vnode;
        C(m, U, U.scopeId, U.slotScopeIds, X.parent);
      }
    }
  }, Y = (m, k, E, B, X, A, U, ee, le = 0) => {
    for (let ue = le; ue < m.length; ue++) {
      const ie = m[ue] = ee ? la(m[ue]) : Cn(m[ue]);
      h(null, ie, k, E, B, X, A, U, ee);
    }
  }, P = (m, k, E, B, X, A, U) => {
    const ee = k.el = m.el;
    let { patchFlag: le, dynamicChildren: ue, dirs: ie } = k;
    le |= m.patchFlag & 16;
    const be = m.props || Xe, xe = k.props || Xe;
    let De;
    if (E && Aa(E, false), (De = xe.onVnodeBeforeUpdate) && ln(De, E, k, m), ie && Ta(k, m, E, "beforeUpdate"), E && Aa(E, true), (be.innerHTML && xe.innerHTML == null || be.textContent && xe.textContent == null) && d(ee, ""), ue ? $(m.dynamicChildren, ue, ee, E, B, No(k, X), A) : U || ae(m, k, ee, null, E, B, No(k, X), A, false), le > 0) {
      if (le & 16) H(ee, be, xe, E, X);
      else if (le & 2 && be.class !== xe.class && l(ee, "class", null, xe.class, X), le & 4 && l(ee, "style", be.style, xe.style, X), le & 8) {
        const Le = k.dynamicProps;
        for (let je = 0; je < Le.length; je++) {
          const ze = Le[je], dt = be[ze], lt = xe[ze];
          (lt !== dt || ze === "value") && l(ee, ze, dt, lt, X, E);
        }
      }
      le & 1 && m.children !== k.children && d(ee, k.children);
    } else !U && ue == null && H(ee, be, xe, E, X);
    ((De = xe.onVnodeUpdated) || ie) && At(() => {
      De && ln(De, E, k, m), ie && Ta(k, m, E, "updated");
    }, B);
  }, $ = (m, k, E, B, X, A, U) => {
    for (let ee = 0; ee < k.length; ee++) {
      const le = m[ee], ue = k[ee], ie = le.el && (le.type === Ce || !ia(le, ue) || le.shapeFlag & 70) ? u(le.el) : E;
      h(le, ue, ie, null, B, X, A, U, true);
    }
  }, H = (m, k, E, B, X) => {
    if (k !== E) {
      if (k !== Xe) for (const A in k) !Er(A) && !(A in E) && l(m, A, k[A], null, X, B);
      for (const A in E) {
        if (Er(A)) continue;
        const U = E[A], ee = k[A];
        U !== ee && A !== "value" && l(m, A, ee, U, X, B);
      }
      "value" in E && l(m, "value", k.value, E.value, X);
    }
  }, z = (m, k, E, B, X, A, U, ee, le) => {
    const ue = k.el = m ? m.el : i(""), ie = k.anchor = m ? m.anchor : i("");
    let { patchFlag: be, dynamicChildren: xe, slotScopeIds: De } = k;
    De && (ee = ee ? ee.concat(De) : De), m == null ? (a(ue, E, B), a(ie, E, B), Y(k.children || [], E, ie, X, A, U, ee, le)) : be > 0 && be & 64 && xe && m.dynamicChildren ? ($(m.dynamicChildren, xe, E, X, A, U, ee), (k.key != null || X && k === X.subTree) && Ui(m, k, true)) : ae(m, k, E, ie, X, A, U, ee, le);
  }, se = (m, k, E, B, X, A, U, ee, le) => {
    k.slotScopeIds = ee, m == null ? k.shapeFlag & 512 ? X.ctx.activate(k, E, B, U, le) : j(k, E, B, X, A, U, le) : y(m, k, le);
  }, j = (m, k, E, B, X, A, U) => {
    const ee = m.component = lv(m, B, X);
    if (no(m) && (ee.ctx.renderer = re), ov(ee, false, U), ee.asyncDep) {
      if (X && X.registerDep(ee, V, U), !m.el) {
        const le = ee.subTree = Ne(Vt);
        I(null, le, k, E);
      }
    } else V(ee, m, k, E, X, A, U);
  }, y = (m, k, E) => {
    const B = k.component = m.component;
    if (Q0(m, k, E)) if (B.asyncDep && !B.asyncResolved) {
      w(B, k, E);
      return;
    } else B.next = k, B.update();
    else k.el = m.el, B.vnode = k;
  }, V = (m, k, E, B, X, A, U) => {
    const ee = () => {
      if (m.isMounted) {
        let { next: be, bu: xe, u: De, parent: Le, vnode: je } = m;
        {
          const xt = fd(m);
          if (xt) {
            be && (be.el = je.el, w(m, be, U)), xt.asyncDep.then(() => {
              m.isUnmounted || ee();
            });
            return;
          }
        }
        let ze = be, dt;
        Aa(m, false), be ? (be.el = je.el, w(m, be, U)) : be = je, xe && Nr(xe), (dt = be.props && be.props.onVnodeBeforeUpdate) && ln(dt, Le, be, je), Aa(m, true);
        const lt = Io(m), $t = m.subTree;
        m.subTree = lt, h($t, lt, u($t.el), D($t), m, X, A), be.el = lt.el, ze === null && X0(m, lt.el), De && At(De, X), (dt = be.props && be.props.onVnodeUpdated) && At(() => ln(dt, Le, be, je), X);
      } else {
        let be;
        const { el: xe, props: De } = k, { bm: Le, m: je, parent: ze, root: dt, type: lt } = m, $t = Ea(k);
        if (Aa(m, false), Le && Nr(Le), !$t && (be = De && De.onVnodeBeforeMount) && ln(be, ze, k), Aa(m, true), xe && O) {
          const xt = () => {
            m.subTree = Io(m), O(xe, m.subTree, m, X, null);
          };
          $t && lt.__asyncHydrate ? lt.__asyncHydrate(xe, m, xt) : xt();
        } else {
          dt.ce && dt.ce._injectChildStyle(lt);
          const xt = m.subTree = Io(m);
          h(null, xt, E, B, m, X, A), k.el = xt.el;
        }
        if (je && At(je, X), !$t && (be = De && De.onVnodeMounted)) {
          const xt = k;
          At(() => ln(be, ze, xt), X);
        }
        (k.shapeFlag & 256 || ze && Ea(ze.vnode) && ze.vnode.shapeFlag & 256) && m.a && At(m.a, X), m.isMounted = true, k = E = B = null;
      }
    };
    m.scope.on();
    const le = m.effect = new yc(ee);
    m.scope.off();
    const ue = m.update = le.run.bind(le), ie = m.job = le.runIfDirty.bind(le);
    ie.i = m, ie.id = m.uid, le.scheduler = () => Ki(ie), Aa(m, true), ue();
  }, w = (m, k, E) => {
    k.component = m;
    const B = m.vnode.props;
    m.vnode = k, m.next = null, E0(m, k.props, B, E), j0(m, k.children, E), ba(), js(m), _a();
  }, ae = (m, k, E, B, X, A, U, ee, le = false) => {
    const ue = m && m.children, ie = m ? m.shapeFlag : 0, be = k.children, { patchFlag: xe, shapeFlag: De } = k;
    if (xe > 0) {
      if (xe & 128) {
        ve(ue, be, E, B, X, A, U, ee, le);
        return;
      } else if (xe & 256) {
        ce(ue, be, E, B, X, A, U, ee, le);
        return;
      }
    }
    De & 8 ? (ie & 16 && G(ue, X, A), be !== ue && d(E, be)) : ie & 16 ? De & 16 ? ve(ue, be, E, B, X, A, U, ee, le) : G(ue, X, A, true) : (ie & 8 && d(E, ""), De & 16 && Y(be, E, B, X, A, U, ee, le));
  }, ce = (m, k, E, B, X, A, U, ee, le) => {
    m = m || tr, k = k || tr;
    const ue = m.length, ie = k.length, be = Math.min(ue, ie);
    let xe;
    for (xe = 0; xe < be; xe++) {
      const De = k[xe] = le ? la(k[xe]) : Cn(k[xe]);
      h(m[xe], De, E, null, X, A, U, ee, le);
    }
    ue > ie ? G(m, X, A, true, false, be) : Y(k, E, B, X, A, U, ee, le, be);
  }, ve = (m, k, E, B, X, A, U, ee, le) => {
    let ue = 0;
    const ie = k.length;
    let be = m.length - 1, xe = ie - 1;
    for (; ue <= be && ue <= xe; ) {
      const De = m[ue], Le = k[ue] = le ? la(k[ue]) : Cn(k[ue]);
      if (ia(De, Le)) h(De, Le, E, null, X, A, U, ee, le);
      else break;
      ue++;
    }
    for (; ue <= be && ue <= xe; ) {
      const De = m[be], Le = k[xe] = le ? la(k[xe]) : Cn(k[xe]);
      if (ia(De, Le)) h(De, Le, E, null, X, A, U, ee, le);
      else break;
      be--, xe--;
    }
    if (ue > be) {
      if (ue <= xe) {
        const De = xe + 1, Le = De < ie ? k[De].el : B;
        for (; ue <= xe; ) h(null, k[ue] = le ? la(k[ue]) : Cn(k[ue]), E, Le, X, A, U, ee, le), ue++;
      }
    } else if (ue > xe) for (; ue <= be; ) q(m[ue], X, A, true), ue++;
    else {
      const De = ue, Le = ue, je = /* @__PURE__ */ new Map();
      for (ue = Le; ue <= xe; ue++) {
        const tt = k[ue] = le ? la(k[ue]) : Cn(k[ue]);
        tt.key != null && je.set(tt.key, ue);
      }
      let ze, dt = 0;
      const lt = xe - Le + 1;
      let $t = false, xt = 0;
      const cn = new Array(lt);
      for (ue = 0; ue < lt; ue++) cn[ue] = 0;
      for (ue = De; ue <= be; ue++) {
        const tt = m[ue];
        if (dt >= lt) {
          q(tt, X, A, true);
          continue;
        }
        let Q;
        if (tt.key != null) Q = je.get(tt.key);
        else for (ze = Le; ze <= xe; ze++) if (cn[ze - Le] === 0 && ia(tt, k[ze])) {
          Q = ze;
          break;
        }
        Q === void 0 ? q(tt, X, A, true) : (cn[Q - Le] = ue + 1, Q >= xt ? xt = Q : $t = true, h(tt, k[Q], E, null, X, A, U, ee, le), dt++);
      }
      const Jt = $t ? q0(cn) : tr;
      for (ze = Jt.length - 1, ue = lt - 1; ue >= 0; ue--) {
        const tt = Le + ue, Q = k[tt], he = tt + 1 < ie ? k[tt + 1].el : B;
        cn[ue] === 0 ? h(null, Q, E, he, X, A, U, ee, le) : $t && (ze < 0 || ue !== Jt[ze] ? _e(Q, E, he, 2) : ze--);
      }
    }
  }, _e = (m, k, E, B, X = null) => {
    const { el: A, type: U, transition: ee, children: le, shapeFlag: ue } = m;
    if (ue & 6) {
      _e(m.component.subTree, k, E, B);
      return;
    }
    if (ue & 128) {
      m.suspense.move(k, E, B);
      return;
    }
    if (ue & 64) {
      U.move(m, k, E, re);
      return;
    }
    if (U === Ce) {
      a(A, k, E);
      for (let ie = 0; ie < le.length; ie++) _e(le[ie], k, E, B);
      a(m.anchor, k, E);
      return;
    }
    if (U === Ol) {
      _(m, k, E);
      return;
    }
    if (B !== 2 && ue & 1 && ee) if (B === 0) ee.beforeEnter(A), a(A, k, E), At(() => ee.enter(A), X);
    else {
      const { leave: ie, delayLeave: be, afterLeave: xe } = ee, De = () => a(A, k, E), Le = () => {
        ie(A, () => {
          De(), xe && xe();
        });
      };
      be ? be(A, De, Le) : Le();
    }
    else a(A, k, E);
  }, q = (m, k, E, B = false, X = false) => {
    const { type: A, props: U, ref: ee, children: le, dynamicChildren: ue, shapeFlag: ie, patchFlag: be, dirs: xe, cacheIndex: De } = m;
    if (be === -2 && (X = false), ee != null && ui(ee, null, E, m, true), De != null && (k.renderCache[De] = void 0), ie & 256) {
      k.ctx.deactivate(m);
      return;
    }
    const Le = ie & 1 && xe, je = !Ea(m);
    let ze;
    if (je && (ze = U && U.onVnodeBeforeUnmount) && ln(ze, k, m), ie & 6) W(m.component, E, B);
    else {
      if (ie & 128) {
        m.suspense.unmount(E, B);
        return;
      }
      Le && Ta(m, null, k, "beforeUnmount"), ie & 64 ? m.type.remove(m, k, E, re, B) : ue && !ue.hasOnce && (A !== Ce || be > 0 && be & 64) ? G(ue, k, E, false, true) : (A === Ce && be & 384 || !X && ie & 16) && G(le, k, E), B && oe(m);
    }
    (je && (ze = U && U.onVnodeUnmounted) || Le) && At(() => {
      ze && ln(ze, k, m), Le && Ta(m, null, k, "unmounted");
    }, E);
  }, oe = (m) => {
    const { type: k, el: E, anchor: B, transition: X } = m;
    if (k === Ce) {
      S(E, B);
      return;
    }
    if (k === Ol) {
      g(m);
      return;
    }
    const A = () => {
      r(E), X && !X.persisted && X.afterLeave && X.afterLeave();
    };
    if (m.shapeFlag & 1 && X && !X.persisted) {
      const { leave: U, delayLeave: ee } = X, le = () => U(E, A);
      ee ? ee(m.el, A, le) : le();
    } else A();
  }, S = (m, k) => {
    let E;
    for (; m !== k; ) E = p(m), r(m), m = E;
    r(k);
  }, W = (m, k, E) => {
    const { bum: B, scope: X, job: A, subTree: U, um: ee, m: le, a: ue } = m;
    Bl(le), Bl(ue), B && Nr(B), X.stop(), A && (A.flags |= 8, q(U, m, k, E)), ee && At(ee, k), At(() => {
      m.isUnmounted = true;
    }, k), k && k.pendingBranch && !k.isUnmounted && m.asyncDep && !m.asyncResolved && m.suspenseId === k.pendingId && (k.deps--, k.deps === 0 && k.resolve());
  }, G = (m, k, E, B = false, X = false, A = 0) => {
    for (let U = A; U < m.length; U++) q(m[U], k, E, B, X);
  }, D = (m) => {
    if (m.shapeFlag & 6) return D(m.component.subTree);
    if (m.shapeFlag & 128) return m.suspense.next();
    const k = p(m.anchor || m.el), E = k && k[Fc];
    return E ? p(E) : k;
  };
  let fe = false;
  const Ae = (m, k, E) => {
    m == null ? k._vnode && q(k._vnode, null, null, true) : h(k._vnode || null, m, k, null, null, null, E), k._vnode = m, fe || (fe = true, js(), Vc(), fe = false);
  }, re = { p: h, um: q, m: _e, r: oe, mt: j, mc: Y, pc: ae, pbc: $, n: D, o: e };
  let Oe, O;
  return { render: Ae, hydrate: Oe, createApp: P0(Ae, Oe) };
}
function No({ type: e, props: t }, n) {
  return n === "svg" && e === "foreignObject" || n === "mathml" && e === "annotation-xml" && t && t.encoding && t.encoding.includes("html") ? void 0 : n;
}
function Aa({ effect: e, job: t }, n) {
  n ? (e.flags |= 32, t.flags |= 4) : (e.flags &= -33, t.flags &= -5);
}
function Y0(e, t) {
  return (!e || e && !e.pendingBranch) && t && !t.persisted;
}
function Ui(e, t, n = false) {
  const a = e.children, r = t.children;
  if ($e(a) && $e(r)) for (let l = 0; l < a.length; l++) {
    const o = a[l];
    let i = r[l];
    i.shapeFlag & 1 && !i.dynamicChildren && ((i.patchFlag <= 0 || i.patchFlag === 32) && (i = r[l] = la(r[l]), i.el = o.el), !n && i.patchFlag !== -2 && Ui(o, i)), i.type === io && (i.el = o.el);
  }
}
function q0(e) {
  const t = e.slice(), n = [0];
  let a, r, l, o, i;
  const s = e.length;
  for (a = 0; a < s; a++) {
    const c = e[a];
    if (c !== 0) {
      if (r = n[n.length - 1], e[r] < c) {
        t[a] = r, n.push(a);
        continue;
      }
      for (l = 0, o = n.length - 1; l < o; ) i = l + o >> 1, e[n[i]] < c ? l = i + 1 : o = i;
      c < e[n[l]] && (l > 0 && (t[a] = n[l - 1]), n[l] = a);
    }
  }
  for (l = n.length, o = n[l - 1]; l-- > 0; ) n[l] = o, o = t[o];
  return n;
}
function fd(e) {
  const t = e.subTree.component;
  if (t) return t.asyncDep && !t.asyncResolved ? t : fd(t);
}
function Bl(e) {
  if (e) for (let t = 0; t < e.length; t++) e[t].flags |= 8;
}
const z0 = Symbol.for("v-scx"), H0 = () => Hn(z0);
function He(e, t, n) {
  return vd(e, t, n);
}
function vd(e, t, n = Xe) {
  const { immediate: a, deep: r, flush: l, once: o } = n, i = Ct({}, n);
  let s;
  if (so) if (l === "sync") {
    const p = H0();
    s = p.__watcherHandles || (p.__watcherHandles = []);
  } else if (!t || a) i.once = true;
  else return { stop: Dn, resume: Dn, pause: Dn };
  const c = Dt;
  i.call = (p, v, b) => yn(p, c, v, b);
  let d = false;
  l === "post" ? i.scheduler = (p) => {
    At(p, c && c.suspense);
  } : l !== "sync" && (d = true, i.scheduler = (p, v) => {
    v ? p() : Ki(p);
  }), i.augmentJob = (p) => {
    t && (p.flags |= 4), d && (p.flags |= 2, c && (p.id = c.uid, p.i = c));
  };
  const u = a0(e, t, i);
  return s && s.push(u), u;
}
function K0(e, t, n) {
  const a = this.proxy, r = rt(e) ? e.includes(".") ? md(a, e) : () => a[e] : e.bind(a, a);
  let l;
  Ee(t) ? l = t : (l = t.handler, n = t);
  const o = il(this), i = vd(r, l.bind(a), n);
  return o(), i;
}
function md(e, t) {
  const n = t.split(".");
  return () => {
    let a = e;
    for (let r = 0; r < n.length && a; r++) a = a[n[r]];
    return a;
  };
}
const Z0 = (e, t) => t === "modelValue" || t === "model-value" ? e.modelModifiers : e[`${t}Modifiers`] || e[`${wn(t)}Modifiers`] || e[`${ya(t)}Modifiers`];
function W0(e, t, ...n) {
  if (e.isUnmounted) return;
  const a = e.vnode.props || Xe;
  let r = n;
  const l = t.startsWith("update:"), o = l && Z0(a, t.slice(7));
  o && (o.trim && (r = n.map((d) => rt(d) ? d.trim() : d)), o.number && (r = n.map(wf)));
  let i, s = a[i = Ao(t)] || a[i = Ao(wn(t))];
  !s && l && (s = a[i = Ao(ya(t))]), s && yn(s, e, 6, r);
  const c = a[i + "Once"];
  if (c) {
    if (!e.emitted) e.emitted = {};
    else if (e.emitted[i]) return;
    e.emitted[i] = true, yn(c, e, 6, r);
  }
}
function hd(e, t, n = false) {
  const a = t.emitsCache, r = a.get(e);
  if (r !== void 0) return r;
  const l = e.emits;
  let o = {}, i = false;
  if (!Ee(e)) {
    const s = (c) => {
      const d = hd(c, t, true);
      d && (i = true, Ct(o, d));
    };
    !n && t.mixins.length && t.mixins.forEach(s), e.extends && s(e.extends), e.mixins && e.mixins.forEach(s);
  }
  return !l && !i ? (et(e) && a.set(e, null), null) : ($e(l) ? l.forEach((s) => o[s] = null) : Ct(o, l), et(e) && a.set(e, o), o);
}
function oo(e, t) {
  return !e || !Ul(t) ? false : (t = t.slice(2).replace(/Once$/, ""), We(e, t[0].toLowerCase() + t.slice(1)) || We(e, ya(t)) || We(e, t));
}
function Io(e) {
  const { type: t, vnode: n, proxy: a, withProxy: r, propsOptions: [l], slots: o, attrs: i, emit: s, render: c, renderCache: d, props: u, data: p, setupState: v, ctx: b, inheritAttrs: h } = e, N = Vl(e);
  let I, x;
  try {
    if (n.shapeFlag & 4) {
      const g = r || a, R = g;
      I = Cn(c.call(R, g, d, u, v, p, b)), x = i;
    } else {
      const g = t;
      I = Cn(g.length > 1 ? g(u, { attrs: i, slots: o, emit: s }) : g(u, null)), x = t.props ? i : U0(i);
    }
  } catch (g) {
    Br.length = 0, to(g, e, 1), I = Ne(Vt);
  }
  let _ = I;
  if (x && h !== false) {
    const g = Object.keys(x), { shapeFlag: R } = _;
    g.length && R & 7 && (l && g.some(Pi) && (x = G0(x, l)), _ = Zn(_, x, false, true));
  }
  return n.dirs && (_ = Zn(_, null, false, true), _.dirs = _.dirs ? _.dirs.concat(n.dirs) : n.dirs), n.transition && sr(_, n.transition), I = _, Vl(N), I;
}
const U0 = (e) => {
  let t;
  for (const n in e) (n === "class" || n === "style" || Ul(n)) && ((t || (t = {}))[n] = e[n]);
  return t;
}, G0 = (e, t) => {
  const n = {};
  for (const a in e) (!Pi(a) || !(a.slice(9) in t)) && (n[a] = e[a]);
  return n;
};
function Q0(e, t, n) {
  const { props: a, children: r, component: l } = e, { props: o, children: i, patchFlag: s } = t, c = l.emitsOptions;
  if (t.dirs || t.transition) return true;
  if (n && s >= 0) {
    if (s & 1024) return true;
    if (s & 16) return a ? Us(a, o, c) : !!o;
    if (s & 8) {
      const d = t.dynamicProps;
      for (let u = 0; u < d.length; u++) {
        const p = d[u];
        if (o[p] !== a[p] && !oo(c, p)) return true;
      }
    }
  } else return (r || i) && (!i || !i.$stable) ? true : a === o ? false : a ? o ? Us(a, o, c) : true : !!o;
  return false;
}
function Us(e, t, n) {
  const a = Object.keys(t);
  if (a.length !== Object.keys(e).length) return true;
  for (let r = 0; r < a.length; r++) {
    const l = a[r];
    if (t[l] !== e[l] && !oo(n, l)) return true;
  }
  return false;
}
function X0({ vnode: e, parent: t }, n) {
  for (; t; ) {
    const a = t.subTree;
    if (a.suspense && a.suspense.activeBranch === e && (a.el = e.el), a === e) (e = t.vnode).el = n, t = t.parent;
    else break;
  }
}
const Fl = (e) => e.__isSuspense;
function J0(e, t) {
  t && t.pendingBranch ? $e(e) ? t.effects.push(...e) : t.effects.push(e) : o0(e);
}
const Ce = Symbol.for("v-fgt"), io = Symbol.for("v-txt"), Vt = Symbol.for("v-cmt"), Ol = Symbol.for("v-stc"), Br = [];
let en = null;
function T(e = false) {
  Br.push(en = e ? null : []);
}
function ev() {
  Br.pop(), en = Br[Br.length - 1] || null;
}
let Gr = 1;
function Gs(e) {
  Gr += e, e < 0 && en && (en.hasOnce = true);
}
function gd(e) {
  return e.dynamicChildren = Gr > 0 ? en || tr : null, ev(), Gr > 0 && en && en.push(e), e;
}
function F(e, t, n, a, r, l) {
  return gd(L(e, t, n, a, r, l, true));
}
function Pe(e, t, n, a, r) {
  return gd(Ne(e, t, n, a, r, true));
}
function Qr(e) {
  return e ? e.__v_isVNode === true : false;
}
function ia(e, t) {
  return e.type === t.type && e.key === t.key;
}
const wd = ({ key: e }) => e ?? null, Pl = ({ ref: e, ref_key: t, ref_for: n }) => (typeof e == "number" && (e = "" + e), e != null ? rt(e) || at(e) || Ee(e) ? { i: St, r: e, k: t, f: !!n } : e : null);
function L(e, t = null, n = null, a = 0, r = null, l = e === Ce ? 0 : 1, o = false, i = false) {
  const s = { __v_isVNode: true, __v_skip: true, type: e, props: t, key: t && wd(t), ref: t && Pl(t), scopeId: Bc, slotScopeIds: null, children: n, component: null, suspense: null, ssContent: null, ssFallback: null, dirs: null, transition: null, el: null, anchor: null, target: null, targetStart: null, targetAnchor: null, staticCount: 0, shapeFlag: l, patchFlag: a, dynamicProps: r, dynamicChildren: null, appContext: null, ctx: St };
  return i ? (Gi(s, n), l & 128 && e.normalize(s)) : n && (s.shapeFlag |= rt(n) ? 8 : 16), Gr > 0 && !o && en && (s.patchFlag > 0 || l & 6) && s.patchFlag !== 32 && en.push(s), s;
}
const Ne = tv;
function tv(e, t = null, n = null, a = 0, r = null, l = false) {
  if ((!e || e === ed) && (e = Vt), Qr(e)) {
    const i = Zn(e, t, true);
    return n && Gi(i, n), Gr > 0 && !l && en && (i.shapeFlag & 6 ? en[en.indexOf(e)] = i : en.push(i)), i.patchFlag = -2, i;
  }
  if (uv(e) && (e = e.__vccOpts), t) {
    t = Zt(t);
    let { class: i, style: s } = t;
    i && !rt(i) && (t.class = pe(i)), et(s) && (Yi(s) && !$e(s) && (s = Ct({}, s)), t.style = Lt(s));
  }
  const o = rt(e) ? 1 : Fl(e) ? 128 : Yc(e) ? 64 : et(e) ? 4 : Ee(e) ? 2 : 0;
  return L(e, t, n, a, r, o, l, true);
}
function Zt(e) {
  return e ? Yi(e) || od(e) ? Ct({}, e) : e : null;
}
function Zn(e, t, n = false, a = false) {
  const { props: r, ref: l, patchFlag: o, children: i, transition: s } = e, c = t ? ft(r || {}, t) : r, d = { __v_isVNode: true, __v_skip: true, type: e.type, props: c, key: c && wd(c), ref: t && t.ref ? n && l ? $e(l) ? l.concat(Pl(t)) : [l, Pl(t)] : Pl(t) : l, scopeId: e.scopeId, slotScopeIds: e.slotScopeIds, children: i, target: e.target, targetStart: e.targetStart, targetAnchor: e.targetAnchor, staticCount: e.staticCount, shapeFlag: e.shapeFlag, patchFlag: t && e.type !== Ce ? o === -1 ? 16 : o | 16 : o, dynamicProps: e.dynamicProps, dynamicChildren: e.dynamicChildren, appContext: e.appContext, dirs: e.dirs, transition: s, component: e.component, suspense: e.suspense, ssContent: e.ssContent && Zn(e.ssContent), ssFallback: e.ssFallback && Zn(e.ssFallback), el: e.el, anchor: e.anchor, ctx: e.ctx, ce: e.ce };
  return s && a && sr(d, s.clone(d)), d;
}
function Ge(e = " ", t = 0) {
  return Ne(io, null, e, t);
}
function nv(e, t) {
  const n = Ne(Ol, null, e);
  return n.staticCount = t, n;
}
function Z(e = "", t = false) {
  return t ? (T(), Pe(Vt, null, e)) : Ne(Vt, null, e);
}
function Cn(e) {
  return e == null || typeof e == "boolean" ? Ne(Vt) : $e(e) ? Ne(Ce, null, e.slice()) : typeof e == "object" ? la(e) : Ne(io, null, String(e));
}
function la(e) {
  return e.el === null && e.patchFlag !== -1 || e.memo ? e : Zn(e);
}
function Gi(e, t) {
  let n = 0;
  const { shapeFlag: a } = e;
  if (t == null) t = null;
  else if ($e(t)) n = 16;
  else if (typeof t == "object") if (a & 65) {
    const r = t.default;
    r && (r._c && (r._d = false), Gi(e, r()), r._c && (r._d = true));
    return;
  } else {
    n = 32;
    const r = t._;
    !r && !od(t) ? t._ctx = St : r === 3 && St && (St.slots._ === 1 ? t._ = 1 : (t._ = 2, e.patchFlag |= 1024));
  }
  else Ee(t) ? (t = { default: t, _ctx: St }, n = 32) : (t = String(t), a & 64 ? (n = 16, t = [Ge(t)]) : n = 8);
  e.children = t, e.shapeFlag |= n;
}
function ft(...e) {
  const t = {};
  for (let n = 0; n < e.length; n++) {
    const a = e[n];
    for (const r in a) if (r === "class") t.class !== a.class && (t.class = pe([t.class, a.class]));
    else if (r === "style") t.style = Lt([t.style, a.style]);
    else if (Ul(r)) {
      const l = t[r], o = a[r];
      o && l !== o && !($e(l) && l.includes(o)) && (t[r] = l ? [].concat(l, o) : o);
    } else r !== "" && (t[r] = a[r]);
  }
  return t;
}
function ln(e, t, n, a = null) {
  yn(e, t, 7, [n, a]);
}
const av = ad();
let rv = 0;
function lv(e, t, n) {
  const a = e.type, r = (t ? t.appContext : e.appContext) || av, l = { uid: rv++, vnode: e, type: a, parent: t, appContext: r, root: null, next: null, subTree: null, effect: null, update: null, job: null, scope: new hc(true), render: null, proxy: null, exposed: null, exposeProxy: null, withProxy: null, provides: t ? t.provides : Object.create(r.provides), ids: t ? t.ids : ["", 0, 0], accessCache: null, renderCache: [], components: null, directives: null, propsOptions: sd(a, r), emitsOptions: hd(a, r), emit: null, emitted: null, propsDefaults: Xe, inheritAttrs: a.inheritAttrs, ctx: Xe, data: Xe, props: Xe, attrs: Xe, slots: Xe, refs: Xe, setupState: Xe, setupContext: null, suspense: n, suspenseId: n ? n.pendingId : 0, asyncDep: null, asyncResolved: false, isMounted: false, isUnmounted: false, isDeactivated: false, bc: null, c: null, bm: null, m: null, bu: null, u: null, um: null, bum: null, da: null, a: null, rtg: null, rtc: null, ec: null, sp: null };
  return l.ctx = { _: l }, l.root = t ? t.root : l, l.emit = W0.bind(null, l), e.ce && e.ce(l), l;
}
let Dt = null;
const xa = () => Dt || St;
let Yl, vi;
{
  const e = pc(), t = (n, a) => {
    let r;
    return (r = e[n]) || (r = e[n] = []), r.push(a), (l) => {
      r.length > 1 ? r.forEach((o) => o(l)) : r[0](l);
    };
  };
  Yl = t("__VUE_INSTANCE_SETTERS__", (n) => Dt = n), vi = t("__VUE_SSR_SETTERS__", (n) => so = n);
}
const il = (e) => {
  const t = Dt;
  return Yl(e), e.scope.on(), () => {
    e.scope.off(), Yl(t);
  };
}, Qs = () => {
  Dt && Dt.scope.off(), Yl(null);
};
function yd(e) {
  return e.vnode.shapeFlag & 4;
}
let so = false;
function ov(e, t = false, n = false) {
  t && vi(t);
  const { props: a, children: r } = e.vnode, l = yd(e);
  R0(e, a, l, t), V0(e, r, n);
  const o = l ? iv(e, t) : void 0;
  return t && vi(false), o;
}
function iv(e, t) {
  const n = e.type;
  e.accessCache = /* @__PURE__ */ Object.create(null), e.proxy = new Proxy(e.ctx, S0);
  const { setup: a } = n;
  if (a) {
    const r = e.setupContext = a.length > 1 ? _d(e) : null, l = il(e);
    ba();
    const o = ll(a, e, 0, [e.props, r]);
    if (_a(), l(), sc(o)) {
      if (Ea(e) || Gc(e), o.then(Qs, Qs), t) return o.then((i) => {
        Xs(e, i, t);
      }).catch((i) => {
        to(i, e, 0);
      });
      e.asyncDep = o;
    } else Xs(e, o, t);
  } else bd(e, t);
}
function Xs(e, t, n) {
  Ee(t) ? e.type.__ssrInlineRender ? e.ssrRender = t : e.render = t : et(t) && (e.setupState = Rc(t)), bd(e, n);
}
let Js;
function bd(e, t, n) {
  const a = e.type;
  if (!e.render) {
    if (!t && Js && !a.render) {
      const r = a.template || Zi(e).template;
      if (r) {
        const { isCustomElement: l, compilerOptions: o } = e.appContext.config, { delimiters: i, compilerOptions: s } = a, c = Ct(Ct({ isCustomElement: l, delimiters: i }, o), s);
        a.render = Js(r, c);
      }
    }
    e.render = a.render || Dn;
  }
  {
    const r = il(e);
    ba();
    try {
      M0(e);
    } finally {
      _a(), r();
    }
  }
}
const sv = { get(e, t) {
  return Ft(e, "get", ""), e[t];
} };
function _d(e) {
  const t = (n) => {
    e.exposed = n || {};
  };
  return { attrs: new Proxy(e.attrs, sv), slots: e.slots, emit: e.emit, expose: t };
}
function uo(e) {
  return e.exposed ? e.exposeProxy || (e.exposeProxy = new Proxy(Rc(qi(e.exposed)), { get(t, n) {
    if (n in t) return t[n];
    if (n in jr) return jr[n](e);
  }, has(t, n) {
    return n in t || n in jr;
  } })) : e.proxy;
}
function mi(e, t = true) {
  return Ee(e) ? e.displayName || e.name : e.name || t && e.__name;
}
function uv(e) {
  return Ee(e) && "__vccOpts" in e;
}
const J = (e, t) => t0(e, t, so);
function xd(e, t, n) {
  const a = arguments.length;
  return a === 2 ? et(t) && !$e(t) ? Qr(t) ? Ne(e, null, [t]) : Ne(e, t) : Ne(e, null, t) : (a > 3 ? n = Array.prototype.slice.call(arguments, 2) : a === 3 && Qr(n) && (n = [n]), Ne(e, t, n));
}
const cv = "3.5.4";
/**
* @vue/runtime-dom v3.5.4
* (c) 2018-present Yuxi (Evan) You and Vue contributors
* @license MIT
**/
let hi;
const eu = typeof window < "u" && window.trustedTypes;
if (eu) try {
  hi = eu.createPolicy("vue", { createHTML: (e) => e });
} catch {
}
const kd = hi ? (e) => hi.createHTML(e) : (e) => e, dv = "http://www.w3.org/2000/svg", pv = "http://www.w3.org/1998/Math/MathML", In = typeof document < "u" ? document : null, tu = In && In.createElement("template"), fv = { insert: (e, t, n) => {
  t.insertBefore(e, n || null);
}, remove: (e) => {
  const t = e.parentNode;
  t && t.removeChild(e);
}, createElement: (e, t, n, a) => {
  const r = t === "svg" ? In.createElementNS(dv, e) : t === "mathml" ? In.createElementNS(pv, e) : n ? In.createElement(e, { is: n }) : In.createElement(e);
  return e === "select" && a && a.multiple != null && r.setAttribute("multiple", a.multiple), r;
}, createText: (e) => In.createTextNode(e), createComment: (e) => In.createComment(e), setText: (e, t) => {
  e.nodeValue = t;
}, setElementText: (e, t) => {
  e.textContent = t;
}, parentNode: (e) => e.parentNode, nextSibling: (e) => e.nextSibling, querySelector: (e) => In.querySelector(e), setScopeId(e, t) {
  e.setAttribute(t, "");
}, insertStaticContent(e, t, n, a, r, l) {
  const o = n ? n.previousSibling : t.lastChild;
  if (r && (r === l || r.nextSibling)) for (; t.insertBefore(r.cloneNode(true), n), !(r === l || !(r = r.nextSibling)); ) ;
  else {
    tu.innerHTML = kd(a === "svg" ? `<svg>${e}</svg>` : a === "mathml" ? `<math>${e}</math>` : e);
    const i = tu.content;
    if (a === "svg" || a === "mathml") {
      const s = i.firstChild;
      for (; s.firstChild; ) i.appendChild(s.firstChild);
      i.removeChild(s);
    }
    t.insertBefore(i, n);
  }
  return [o ? o.nextSibling : t.firstChild, n ? n.previousSibling : t.lastChild];
} }, Xn = "transition", Cr = "animation", Xr = Symbol("_vtc"), Sd = { name: String, type: String, css: { type: Boolean, default: true }, duration: [String, Number, Object], enterFromClass: String, enterActiveClass: String, enterToClass: String, appearFromClass: String, appearActiveClass: String, appearToClass: String, leaveFromClass: String, leaveActiveClass: String, leaveToClass: String }, vv = Ct({}, Hc, Sd), mv = (e) => (e.displayName = "Transition", e.props = vv, e), _r = mv((e, { slots: t }) => xd(p0, hv(e), t)), Da = (e, t = []) => {
  $e(e) ? e.forEach((n) => n(...t)) : e && e(...t);
}, nu = (e) => e ? $e(e) ? e.some((t) => t.length > 1) : e.length > 1 : false;
function hv(e) {
  const t = {};
  for (const z in e) z in Sd || (t[z] = e[z]);
  if (e.css === false) return t;
  const { name: n = "v", type: a, duration: r, enterFromClass: l = `${n}-enter-from`, enterActiveClass: o = `${n}-enter-active`, enterToClass: i = `${n}-enter-to`, appearFromClass: s = l, appearActiveClass: c = o, appearToClass: d = i, leaveFromClass: u = `${n}-leave-from`, leaveActiveClass: p = `${n}-leave-active`, leaveToClass: v = `${n}-leave-to` } = e, b = gv(r), h = b && b[0], N = b && b[1], { onBeforeEnter: I, onEnter: x, onEnterCancelled: _, onLeave: g, onLeaveCancelled: R, onBeforeAppear: M = I, onAppear: C = x, onAppearCancelled: Y = _ } = t, P = (z, se, j) => {
    La(z, se ? d : i), La(z, se ? c : o), j && j();
  }, $ = (z, se) => {
    z._isLeaving = false, La(z, u), La(z, v), La(z, p), se && se();
  }, H = (z) => (se, j) => {
    const y = z ? C : x, V = () => P(se, z, j);
    Da(y, [se, V]), au(() => {
      La(se, z ? s : l), Jn(se, z ? d : i), nu(y) || ru(se, a, h, V);
    });
  };
  return Ct(t, { onBeforeEnter(z) {
    Da(I, [z]), Jn(z, l), Jn(z, o);
  }, onBeforeAppear(z) {
    Da(M, [z]), Jn(z, s), Jn(z, c);
  }, onEnter: H(false), onAppear: H(true), onLeave(z, se) {
    z._isLeaving = true;
    const j = () => $(z, se);
    Jn(z, u), Jn(z, p), bv(), au(() => {
      z._isLeaving && (La(z, u), Jn(z, v), nu(g) || ru(z, a, N, j));
    }), Da(g, [z, j]);
  }, onEnterCancelled(z) {
    P(z, false), Da(_, [z]);
  }, onAppearCancelled(z) {
    P(z, true), Da(Y, [z]);
  }, onLeaveCancelled(z) {
    $(z), Da(R, [z]);
  } });
}
function gv(e) {
  if (e == null) return null;
  if (et(e)) return [Vo(e.enter), Vo(e.leave)];
  {
    const t = Vo(e);
    return [t, t];
  }
}
function Vo(e) {
  return yf(e);
}
function Jn(e, t) {
  t.split(/\s+/).forEach((n) => n && e.classList.add(n)), (e[Xr] || (e[Xr] = /* @__PURE__ */ new Set())).add(t);
}
function La(e, t) {
  t.split(/\s+/).forEach((a) => a && e.classList.remove(a));
  const n = e[Xr];
  n && (n.delete(t), n.size || (e[Xr] = void 0));
}
function au(e) {
  requestAnimationFrame(() => {
    requestAnimationFrame(e);
  });
}
let wv = 0;
function ru(e, t, n, a) {
  const r = e._endId = ++wv, l = () => {
    r === e._endId && a();
  };
  if (n) return setTimeout(l, n);
  const { type: o, timeout: i, propCount: s } = yv(e, t);
  if (!o) return a();
  const c = o + "end";
  let d = 0;
  const u = () => {
    e.removeEventListener(c, p), l();
  }, p = (v) => {
    v.target === e && ++d >= s && u();
  };
  setTimeout(() => {
    d < s && u();
  }, i + 1), e.addEventListener(c, p);
}
function yv(e, t) {
  const n = window.getComputedStyle(e), a = (b) => (n[b] || "").split(", "), r = a(`${Xn}Delay`), l = a(`${Xn}Duration`), o = lu(r, l), i = a(`${Cr}Delay`), s = a(`${Cr}Duration`), c = lu(i, s);
  let d = null, u = 0, p = 0;
  t === Xn ? o > 0 && (d = Xn, u = o, p = l.length) : t === Cr ? c > 0 && (d = Cr, u = c, p = s.length) : (u = Math.max(o, c), d = u > 0 ? o > c ? Xn : Cr : null, p = d ? d === Xn ? l.length : s.length : 0);
  const v = d === Xn && /\b(transform|all)(,|$)/.test(a(`${Xn}Property`).toString());
  return { type: d, timeout: u, propCount: p, hasTransform: v };
}
function lu(e, t) {
  for (; e.length < t.length; ) e = e.concat(e);
  return Math.max(...t.map((n, a) => ou(n) + ou(e[a])));
}
function ou(e) {
  return e === "auto" ? 0 : Number(e.slice(0, -1).replace(",", ".")) * 1e3;
}
function bv() {
  return document.body.offsetHeight;
}
function _v(e, t, n) {
  const a = e[Xr];
  a && (t = (t ? [t, ...a] : [...a]).join(" ")), t == null ? e.removeAttribute("class") : n ? e.setAttribute("class", t) : e.className = t;
}
const ql = Symbol("_vod"), Cd = Symbol("_vsh"), ca = { beforeMount(e, { value: t }, { transition: n }) {
  e[ql] = e.style.display === "none" ? "" : e.style.display, n && t ? n.beforeEnter(e) : Mr(e, t);
}, mounted(e, { value: t }, { transition: n }) {
  n && t && n.enter(e);
}, updated(e, { value: t, oldValue: n }, { transition: a }) {
  !t != !n && (a ? t ? (a.beforeEnter(e), Mr(e, true), a.enter(e)) : a.leave(e, () => {
    Mr(e, false);
  }) : Mr(e, t));
}, beforeUnmount(e, { value: t }) {
  Mr(e, t);
} };
function Mr(e, t) {
  e.style.display = t ? e[ql] : "none", e[Cd] = !t;
}
const xv = Symbol(""), kv = /(^|;)\s*display\s*:/;
function Sv(e, t, n) {
  const a = e.style, r = rt(n);
  let l = false;
  if (n && !r) {
    if (t) if (rt(t)) for (const o of t.split(";")) {
      const i = o.slice(0, o.indexOf(":")).trim();
      n[i] == null && $l(a, i, "");
    }
    else for (const o in t) n[o] == null && $l(a, o, "");
    for (const o in n) o === "display" && (l = true), $l(a, o, n[o]);
  } else if (r) {
    if (t !== n) {
      const o = a[xv];
      o && (n += ";" + o), a.cssText = n, l = kv.test(n);
    }
  } else t && e.removeAttribute("style");
  ql in e && (e[ql] = l ? a.display : "", e[Cd] && (a.display = "none"));
}
const iu = /\s*!important$/;
function $l(e, t, n) {
  if ($e(n)) n.forEach((a) => $l(e, t, a));
  else if (n == null && (n = ""), t.startsWith("--")) e.setProperty(t, n);
  else {
    const a = Cv(e, t);
    iu.test(n) ? e.setProperty(ya(a), n.replace(iu, ""), "important") : e[a] = n;
  }
}
const su = ["Webkit", "Moz", "ms"], jo = {};
function Cv(e, t) {
  const n = jo[t];
  if (n) return n;
  let a = wn(t);
  if (a !== "filter" && a in e) return jo[t] = a;
  a = Ql(a);
  for (let r = 0; r < su.length; r++) {
    const l = su[r] + a;
    if (l in e) return jo[t] = l;
  }
  return t;
}
const uu = "http://www.w3.org/1999/xlink";
function cu(e, t, n, a, r, l = Cf(t)) {
  a && t.startsWith("xlink:") ? n == null ? e.removeAttributeNS(uu, t.slice(6, t.length)) : e.setAttributeNS(uu, t, n) : n == null || l && !fc(n) ? e.removeAttribute(t) : e.setAttribute(t, l ? "" : wa(n) ? String(n) : n);
}
function Mv(e, t, n, a) {
  if (t === "innerHTML" || t === "textContent") {
    n != null && (e[t] = t === "innerHTML" ? kd(n) : n);
    return;
  }
  const r = e.tagName;
  if (t === "value" && r !== "PROGRESS" && !r.includes("-")) {
    const o = r === "OPTION" ? e.getAttribute("value") || "" : e.value, i = n == null ? e.type === "checkbox" ? "on" : "" : String(n);
    (o !== i || !("_value" in e)) && (e.value = i), n == null && e.removeAttribute(t), e._value = n;
    return;
  }
  let l = false;
  if (n === "" || n == null) {
    const o = typeof e[t];
    o === "boolean" ? n = fc(n) : n == null && o === "string" ? (n = "", l = true) : o === "number" && (n = 0, l = true);
  }
  try {
    e[t] = n;
  } catch {
  }
  l && e.removeAttribute(t);
}
function Tv(e, t, n, a) {
  e.addEventListener(t, n, a);
}
function Av(e, t, n, a) {
  e.removeEventListener(t, n, a);
}
const du = Symbol("_vei");
function Dv(e, t, n, a, r = null) {
  const l = e[du] || (e[du] = {}), o = l[t];
  if (a && o) o.value = a;
  else {
    const [i, s] = Lv(t);
    if (a) {
      const c = l[t] = $v(a, r);
      Tv(e, i, c, s);
    } else o && (Av(e, i, o, s), l[t] = void 0);
  }
}
const pu = /(?:Once|Passive|Capture)$/;
function Lv(e) {
  let t;
  if (pu.test(e)) {
    t = {};
    let n;
    for (; n = e.match(pu); ) e = e.slice(0, e.length - n[0].length), t[n[0].toLowerCase()] = true;
  }
  return [e[2] === ":" ? e.slice(3) : ya(e.slice(2)), t];
}
let Bo = 0;
const Ov = Promise.resolve(), Pv = () => Bo || (Ov.then(() => Bo = 0), Bo = Date.now());
function $v(e, t) {
  const n = (a) => {
    if (!a._vts) a._vts = Date.now();
    else if (a._vts <= n.attached) return;
    yn(Rv(a, n.value), t, 5, [a]);
  };
  return n.value = e, n.attached = Pv(), n;
}
function Rv(e, t) {
  if ($e(t)) {
    const n = e.stopImmediatePropagation;
    return e.stopImmediatePropagation = () => {
      n.call(e), e._stopped = true;
    }, t.map((a) => (r) => !r._stopped && a && a(r));
  } else return t;
}
const fu = (e) => e.charCodeAt(0) === 111 && e.charCodeAt(1) === 110 && e.charCodeAt(2) > 96 && e.charCodeAt(2) < 123, Ev = (e, t, n, a, r, l) => {
  const o = r === "svg";
  t === "class" ? _v(e, a, o) : t === "style" ? Sv(e, n, a) : Ul(t) ? Pi(t) || Dv(e, t, n, a, l) : (t[0] === "." ? (t = t.slice(1), true) : t[0] === "^" ? (t = t.slice(1), false) : Nv(e, t, a, o)) ? (Mv(e, t, a), !e.tagName.includes("-") && (t === "value" || t === "checked" || t === "selected") && cu(e, t, a, o, l, t !== "value")) : (t === "true-value" ? e._trueValue = a : t === "false-value" && (e._falseValue = a), cu(e, t, a, o));
};
function Nv(e, t, n, a) {
  if (a) return !!(t === "innerHTML" || t === "textContent" || t in e && fu(t) && Ee(n));
  if (t === "spellcheck" || t === "draggable" || t === "translate" || t === "form" || t === "list" && e.tagName === "INPUT" || t === "type" && e.tagName === "TEXTAREA") return false;
  if (t === "width" || t === "height") {
    const r = e.tagName;
    if (r === "IMG" || r === "VIDEO" || r === "CANVAS" || r === "SOURCE") return false;
  }
  return fu(t) && rt(n) ? false : !!(t in e || e._isVueCE && (/[A-Z]/.test(t) || !rt(n)));
}
const Iv = ["ctrl", "shift", "alt", "meta"], Vv = { stop: (e) => e.stopPropagation(), prevent: (e) => e.preventDefault(), self: (e) => e.target !== e.currentTarget, ctrl: (e) => !e.ctrlKey, shift: (e) => !e.shiftKey, alt: (e) => !e.altKey, meta: (e) => !e.metaKey, left: (e) => "button" in e && e.button !== 0, middle: (e) => "button" in e && e.button !== 1, right: (e) => "button" in e && e.button !== 2, exact: (e, t) => Iv.some((n) => e[`${n}Key`] && !t.includes(n)) }, da = (e, t) => {
  const n = e._withMods || (e._withMods = {}), a = t.join(".");
  return n[a] || (n[a] = (r, ...l) => {
    for (let o = 0; o < t.length; o++) {
      const i = Vv[t[o]];
      if (i && i(r, t)) return;
    }
    return e(r, ...l);
  });
}, jv = { esc: "escape", space: " ", up: "arrow-up", left: "arrow-left", right: "arrow-right", down: "arrow-down", delete: "backspace" }, gi = (e, t) => {
  const n = e._withKeys || (e._withKeys = {}), a = t.join(".");
  return n[a] || (n[a] = (r) => {
    if (!("key" in r)) return;
    const l = ya(r.key);
    if (t.some((o) => o === l || jv[o] === l)) return e(r);
  });
}, Bv = Ct({ patchProp: Ev }, fv);
let vu;
function Md() {
  return vu || (vu = B0(Bv));
}
const mu = (...e) => {
  Md().render(...e);
}, Fv = (...e) => {
  const t = Md().createApp(...e), { mount: n } = t;
  return t.mount = (a) => {
    const r = qv(a);
    if (!r) return;
    const l = t._component;
    !Ee(l) && !l.render && !l.template && (l.template = r.innerHTML), r.nodeType === 1 && (r.textContent = "");
    const o = n(r, false, Yv(r));
    return r instanceof Element && (r.removeAttribute("v-cloak"), r.setAttribute("data-v-app", "")), o;
  }, t;
};
function Yv(e) {
  if (e instanceof SVGElement) return "svg";
  if (typeof MathMLElement == "function" && e instanceof MathMLElement) return "mathml";
}
function qv(e) {
  return rt(e) ? document.querySelector(e) : e;
}
var zv = false;
/*!
* pinia v2.2.2
* (c) 2024 Eduardo San Martin Morote
* @license MIT
*/
let Td;
const co = (e) => Td = e, Ad = Symbol();
function wi(e) {
  return e && typeof e == "object" && Object.prototype.toString.call(e) === "[object Object]" && typeof e.toJSON != "function";
}
var Fr;
(function(e) {
  e.direct = "direct", e.patchObject = "patch object", e.patchFunction = "patch function";
})(Fr || (Fr = {}));
function Hv() {
  const e = gc(true), t = e.run(() => te({}));
  let n = [], a = [];
  const r = qi({ install(l) {
    co(r), r._a = l, l.provide(Ad, r), l.config.globalProperties.$pinia = r, a.forEach((o) => n.push(o)), a = [];
  }, use(l) {
    return !this._a && !zv ? a.push(l) : n.push(l), this;
  }, _p: n, _a: null, _e: e, _s: /* @__PURE__ */ new Map(), state: t });
  return r;
}
const Dd = () => {
};
function hu(e, t, n, a = Dd) {
  e.push(t);
  const r = () => {
    const l = e.indexOf(t);
    l > -1 && (e.splice(l, 1), a());
  };
  return !n && Ei() && wc(r), r;
}
function Wa(e, ...t) {
  e.slice().forEach((n) => {
    n(...t);
  });
}
const Kv = (e) => e(), gu = Symbol(), Fo = Symbol();
function yi(e, t) {
  e instanceof Map && t instanceof Map ? t.forEach((n, a) => e.set(a, n)) : e instanceof Set && t instanceof Set && t.forEach(e.add, e);
  for (const n in t) {
    if (!t.hasOwnProperty(n)) continue;
    const a = t[n], r = e[n];
    wi(r) && wi(a) && e.hasOwnProperty(n) && !at(a) && !qn(a) ? e[n] = yi(r, a) : e[n] = a;
  }
  return e;
}
const Zv = Symbol();
function Wv(e) {
  return !wi(e) || !e.hasOwnProperty(Zv);
}
const { assign: na } = Object;
function Uv(e) {
  return !!(at(e) && e.effect);
}
function Gv(e, t, n, a) {
  const { state: r, actions: l, getters: o } = t, i = n.state.value[e];
  let s;
  function c() {
    i || (n.state.value[e] = r ? r() : {});
    const d = Pt(n.state.value[e]);
    return na(d, l, Object.keys(o || {}).reduce((u, p) => (u[p] = qi(J(() => {
      co(n);
      const v = n._s.get(e);
      return o[p].call(v, v);
    })), u), {}));
  }
  return s = Ld(e, c, t, n, a, true), s;
}
function Ld(e, t, n = {}, a, r, l) {
  let o;
  const i = na({ actions: {} }, n), s = { deep: true };
  let c, d, u = [], p = [], v;
  const b = a.state.value[e];
  !l && !b && (a.state.value[e] = {}), te({});
  let h;
  function N(C) {
    let Y;
    c = d = false, typeof C == "function" ? (C(a.state.value[e]), Y = { type: Fr.patchFunction, storeId: e, events: v }) : (yi(a.state.value[e], C), Y = { type: Fr.patchObject, payload: C, storeId: e, events: v });
    const P = h = Symbol();
    bt().then(() => {
      h === P && (c = true);
    }), d = true, Wa(u, Y, a.state.value[e]);
  }
  const I = l ? function() {
    const { state: C } = n, Y = C ? C() : {};
    this.$patch((P) => {
      na(P, Y);
    });
  } : Dd;
  function x() {
    o.stop(), u = [], p = [], a._s.delete(e);
  }
  const _ = (C, Y = "") => {
    if (gu in C) return C[Fo] = Y, C;
    const P = function() {
      co(a);
      const $ = Array.from(arguments), H = [], z = [];
      function se(V) {
        H.push(V);
      }
      function j(V) {
        z.push(V);
      }
      Wa(p, { args: $, name: P[Fo], store: R, after: se, onError: j });
      let y;
      try {
        y = C.apply(this && this.$id === e ? this : R, $);
      } catch (V) {
        throw Wa(z, V), V;
      }
      return y instanceof Promise ? y.then((V) => (Wa(H, V), V)).catch((V) => (Wa(z, V), Promise.reject(V))) : (Wa(H, y), y);
    };
    return P[gu] = true, P[Fo] = Y, P;
  }, g = { _p: a, $id: e, $onAction: hu.bind(null, p), $patch: N, $reset: I, $subscribe(C, Y = {}) {
    const P = hu(u, C, Y.detached, () => $()), $ = o.run(() => He(() => a.state.value[e], (H) => {
      (Y.flush === "sync" ? d : c) && C({ storeId: e, type: Fr.direct, events: v }, H);
    }, na({}, s, Y)));
    return P;
  }, $dispose: x }, R = un(g);
  a._s.set(e, R);
  const M = (a._a && a._a.runWithContext || Kv)(() => a._e.run(() => (o = gc()).run(() => t({ action: _ }))));
  for (const C in M) {
    const Y = M[C];
    if (at(Y) && !Uv(Y) || qn(Y)) l || (b && Wv(Y) && (at(Y) ? Y.value = b[C] : yi(Y, b[C])), a.state.value[e][C] = Y);
    else if (typeof Y == "function") {
      const P = _(Y, C);
      M[C] = P, i.actions[C] = Y;
    }
  }
  return na(R, M), na(Fe(R), M), Object.defineProperty(R, "$state", { get: () => a.state.value[e], set: (C) => {
    N((Y) => {
      na(Y, C);
    });
  } }), a._p.forEach((C) => {
    na(R, o.run(() => C({ store: R, app: a._a, pinia: a, options: i })));
  }), b && l && n.hydrate && n.hydrate(R.$state, b), c = true, d = true, R;
}
function sl(e, t, n) {
  let a, r;
  const l = typeof t == "function";
  typeof e == "string" ? (a = e, r = l ? n : t) : (r = e, a = e.id);
  function o(i, s) {
    const c = $0();
    return i = i || (c ? Hn(Ad, null) : null), i && co(i), i = Td, i._s.has(a) || (l ? Ld(a, t, r, i) : Gv(a, r, i)), i._s.get(a);
  }
  return o.$id = a, o;
}
function Ya(e) {
  {
    e = Fe(e);
    const t = {};
    for (const n in e) {
      const a = e[n];
      (at(a) || qn(a)) && (t[n] = ir(e, n));
    }
    return t;
  }
}
var Qv = Object.defineProperty, Xv = (e, t, n) => t in e ? Qv(e, t, { enumerable: true, configurable: true, writable: true, value: n }) : e[t] = n, Me = (e, t, n) => Xv(e, typeof t != "symbol" ? t + "" : t, n);
const Ha = sl("component", { state: () => ({ currentComponent: null }), actions: { setCurrentComponent(e) {
  this.currentComponent = e;
} } });
var Jv = (e, t) => {
  const n = e.__vccOpts || e;
  for (const [a, r] of t) n[a] = r;
  return n;
};
const e1 = { name: "HollowDotsSpinner", props: { animationDuration: { type: Number, default: 1e3 }, dotSize: { type: Number, default: 15 }, dotsNum: { type: Number, default: 3 }, color: { type: String, default: "#fff" } }, computed: { horizontalMargin() {
  return this.dotSize / 2;
}, spinnerStyle() {
  return { height: `${this.dotSize}px`, width: `${(this.dotSize + this.horizontalMargin * 2) * this.dotsNum}px` };
}, dotStyle() {
  return { animationDuration: `${this.animationDuration}ms`, width: `${this.dotSize}px`, height: `${this.dotSize}px`, margin: `0 ${this.horizontalMargin}px`, borderWidth: `${this.dotSize / 5}px`, borderColor: this.color };
}, dotsStyles() {
  const e = [], t = this.animationDuration;
  for (let n = 1; n <= this.dotsNum; n++) e.push({ animationDelay: `${t * n * 0.3}ms`, ...this.dotStyle });
  return e;
} } };
function t1(e, t, n, a, r, l) {
  return T(), F("div", { class: "hollow-dots-spinner", style: Lt(l.spinnerStyle) }, [(T(true), F(Ce, null, Ve(l.dotsStyles, (o, i) => (T(), F("div", { key: i, class: "dot", style: Lt(o) }, null, 4))), 128))], 4);
}
var Od = Jv(e1, [["render", t1]]), n1 = {};
(function(e) {
  (function() {
    var t = { not_string: /[^s]/, not_bool: /[^t]/, not_type: /[^T]/, not_primitive: /[^v]/, number: /[diefg]/, numeric_arg: /[bcdiefguxX]/, json: /[j]/, not_json: /[^j]/, text: /^[^\x25]+/, modulo: /^\x25{2}/, placeholder: /^\x25(?:([1-9]\d*)\$|\(([^)]+)\))?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-gijostTuvxX])/, key: /^([a-z_][a-z_\d]*)/i, key_access: /^\.([a-z_][a-z_\d]*)/i, index_access: /^\[(\d+)\]/, sign: /^[+-]/ };
    function n(i) {
      return r(o(i), arguments);
    }
    function a(i, s) {
      return n.apply(null, [i].concat(s || []));
    }
    function r(i, s) {
      var c = 1, d = i.length, u, p = "", v, b, h, N, I, x, _, g;
      for (v = 0; v < d; v++) if (typeof i[v] == "string") p += i[v];
      else if (typeof i[v] == "object") {
        if (h = i[v], h.keys) for (u = s[c], b = 0; b < h.keys.length; b++) {
          if (u == null) throw new Error(n('[sprintf] Cannot access property "%s" of undefined value "%s"', h.keys[b], h.keys[b - 1]));
          u = u[h.keys[b]];
        }
        else h.param_no ? u = s[h.param_no] : u = s[c++];
        if (t.not_type.test(h.type) && t.not_primitive.test(h.type) && u instanceof Function && (u = u()), t.numeric_arg.test(h.type) && typeof u != "number" && isNaN(u)) throw new TypeError(n("[sprintf] expecting number but found %T", u));
        switch (t.number.test(h.type) && (_ = u >= 0), h.type) {
          case "b":
            u = parseInt(u, 10).toString(2);
            break;
          case "c":
            u = String.fromCharCode(parseInt(u, 10));
            break;
          case "d":
          case "i":
            u = parseInt(u, 10);
            break;
          case "j":
            u = JSON.stringify(u, null, h.width ? parseInt(h.width) : 0);
            break;
          case "e":
            u = h.precision ? parseFloat(u).toExponential(h.precision) : parseFloat(u).toExponential();
            break;
          case "f":
            u = h.precision ? parseFloat(u).toFixed(h.precision) : parseFloat(u);
            break;
          case "g":
            u = h.precision ? String(Number(u.toPrecision(h.precision))) : parseFloat(u);
            break;
          case "o":
            u = (parseInt(u, 10) >>> 0).toString(8);
            break;
          case "s":
            u = String(u), u = h.precision ? u.substring(0, h.precision) : u;
            break;
          case "t":
            u = String(!!u), u = h.precision ? u.substring(0, h.precision) : u;
            break;
          case "T":
            u = Object.prototype.toString.call(u).slice(8, -1).toLowerCase(), u = h.precision ? u.substring(0, h.precision) : u;
            break;
          case "u":
            u = parseInt(u, 10) >>> 0;
            break;
          case "v":
            u = u.valueOf(), u = h.precision ? u.substring(0, h.precision) : u;
            break;
          case "x":
            u = (parseInt(u, 10) >>> 0).toString(16);
            break;
          case "X":
            u = (parseInt(u, 10) >>> 0).toString(16).toUpperCase();
            break;
        }
        t.json.test(h.type) ? p += u : (t.number.test(h.type) && (!_ || h.sign) ? (g = _ ? "+" : "-", u = u.toString().replace(t.sign, "")) : g = "", I = h.pad_char ? h.pad_char === "0" ? "0" : h.pad_char.charAt(1) : " ", x = h.width - (g + u).length, N = h.width && x > 0 ? I.repeat(x) : "", p += h.align ? g + u + N : I === "0" ? g + N + u : N + g + u);
      }
      return p;
    }
    var l = /* @__PURE__ */ Object.create(null);
    function o(i) {
      if (l[i]) return l[i];
      for (var s = i, c, d = [], u = 0; s; ) {
        if ((c = t.text.exec(s)) !== null) d.push(c[0]);
        else if ((c = t.modulo.exec(s)) !== null) d.push("%");
        else if ((c = t.placeholder.exec(s)) !== null) {
          if (c[2]) {
            u |= 1;
            var p = [], v = c[2], b = [];
            if ((b = t.key.exec(v)) !== null) for (p.push(b[1]); (v = v.substring(b[0].length)) !== ""; ) if ((b = t.key_access.exec(v)) !== null) p.push(b[1]);
            else if ((b = t.index_access.exec(v)) !== null) p.push(b[1]);
            else throw new SyntaxError("[sprintf] failed to parse named argument key");
            else throw new SyntaxError("[sprintf] failed to parse named argument key");
            c[2] = p;
          } else u |= 2;
          if (u === 3) throw new Error("[sprintf] mixing positional and named placeholders is not (yet) supported");
          d.push({ placeholder: c[0], param_no: c[1], keys: c[2], sign: c[3], pad_char: c[4], align: c[5], width: c[6], precision: c[7], type: c[8] });
        } else throw new SyntaxError("[sprintf] unexpected placeholder");
        s = s.substring(c[0].length);
      }
      return l[i] = d;
    }
    e.sprintf = n, e.vsprintf = a, typeof window < "u" && (window.sprintf = n, window.vsprintf = a);
  })();
})(n1);
var bi, Pd, Rr, $d;
bi = { "(": 9, "!": 8, "*": 7, "/": 7, "%": 7, "+": 6, "-": 6, "<": 5, "<=": 5, ">": 5, ">=": 5, "==": 4, "!=": 4, "&&": 3, "||": 2, "?": 1, "?:": 1 };
Pd = ["(", "?"];
Rr = { ")": ["("], ":": ["?", "?:"] };
$d = /<=|>=|==|!=|&&|\|\||\?:|\(|!|\*|\/|%|\+|-|<|>|\?|\)|:/;
function a1(e) {
  for (var t = [], n = [], a, r, l, o; a = e.match($d); ) {
    for (r = a[0], l = e.substr(0, a.index).trim(), l && t.push(l); o = n.pop(); ) {
      if (Rr[r]) {
        if (Rr[r][0] === o) {
          r = Rr[r][1] || r;
          break;
        }
      } else if (Pd.indexOf(o) >= 0 || bi[o] < bi[r]) {
        n.push(o);
        break;
      }
      t.push(o);
    }
    Rr[r] || n.push(r), e = e.substr(a.index + r.length);
  }
  return e = e.trim(), e && t.push(e), t.concat(n.reverse());
}
var r1 = { "!": function(e) {
  return !e;
}, "*": function(e, t) {
  return e * t;
}, "/": function(e, t) {
  return e / t;
}, "%": function(e, t) {
  return e % t;
}, "+": function(e, t) {
  return e + t;
}, "-": function(e, t) {
  return e - t;
}, "<": function(e, t) {
  return e < t;
}, "<=": function(e, t) {
  return e <= t;
}, ">": function(e, t) {
  return e > t;
}, ">=": function(e, t) {
  return e >= t;
}, "==": function(e, t) {
  return e === t;
}, "!=": function(e, t) {
  return e !== t;
}, "&&": function(e, t) {
  return e && t;
}, "||": function(e, t) {
  return e || t;
}, "?:": function(e, t, n) {
  if (e) throw t;
  return n;
} };
function l1(e, t) {
  var n = [], a, r, l, o, i, s;
  for (a = 0; a < e.length; a++) {
    if (i = e[a], o = r1[i], o) {
      for (r = o.length, l = Array(r); r--; ) l[r] = n.pop();
      try {
        s = o.apply(null, l);
      } catch (c) {
        return c;
      }
    } else t.hasOwnProperty(i) ? s = t[i] : s = +i;
    n.push(s);
  }
  return n[0];
}
function o1(e) {
  var t = a1(e);
  return function(n) {
    return l1(t, n);
  };
}
function i1(e) {
  var t = o1(e);
  return function(n) {
    return +t({ n });
  };
}
var wu = { contextDelimiter: "", onMissingKey: null };
function s1(e) {
  var t, n, a;
  for (t = e.split(";"), n = 0; n < t.length; n++) if (a = t[n].trim(), a.indexOf("plural=") === 0) return a.substr(7);
}
function Qi(e, t) {
  var n;
  this.data = e, this.pluralForms = {}, this.options = {};
  for (n in wu) this.options[n] = t !== void 0 && n in t ? t[n] : wu[n];
}
Qi.prototype.getPluralForm = function(e, t) {
  var n = this.pluralForms[e], a, r, l;
  return n || (a = this.data[e][""], l = a["Plural-Forms"] || a["plural-forms"] || a.plural_forms, typeof l != "function" && (r = s1(a["Plural-Forms"] || a["plural-forms"] || a.plural_forms), l = i1(r)), n = this.pluralForms[e] = l), n(t);
};
Qi.prototype.dcnpgettext = function(e, t, n, a, r) {
  var l, o, i;
  return r === void 0 ? l = 0 : l = this.getPluralForm(e, r), o = n, t && (o = t + this.options.contextDelimiter + n), i = this.data[e][o], i && i[l] ? i[l] : (this.options.onMissingKey && this.options.onMissingKey(n, e), l === 0 ? n : a);
};
const yu = { "": { plural_forms(e) {
  return e === 1 ? 0 : 1;
} } }, u1 = /^i18n\.(n?gettext|has_translation)(_|$)/, c1 = (e, t, n) => {
  const a = new Qi({}), r = /* @__PURE__ */ new Set(), l = () => {
    r.forEach((g) => g());
  }, o = (g) => (r.add(g), () => r.delete(g)), i = (g = "default") => a.data[g], s = (g, R = "default") => {
    var M;
    a.data[R] = { ...a.data[R], ...g }, a.data[R][""] = { ...yu[""], ...(M = a.data[R]) == null ? void 0 : M[""] }, delete a.pluralForms[R];
  }, c = (g, R) => {
    s(g, R), l();
  }, d = (g, R = "default") => {
    var M;
    a.data[R] = { ...a.data[R], ...g, "": { ...yu[""], ...(M = a.data[R]) == null ? void 0 : M[""], ...g == null ? void 0 : g[""] } }, delete a.pluralForms[R], l();
  }, u = (g, R) => {
    a.data = {}, a.pluralForms = {}, c(g, R);
  }, p = (g = "default", R, M, C, Y) => (a.data[g] || s(void 0, g), a.dcnpgettext(g, R, M, C, Y)), v = (g = "default") => g, b = (g, R) => {
    let M = p(R, void 0, g);
    return n ? (M = n.applyFilters("i18n.gettext", M, g, R), n.applyFilters("i18n.gettext_" + v(R), M, g, R)) : M;
  }, h = (g, R, M) => {
    let C = p(M, R, g);
    return n ? (C = n.applyFilters("i18n.gettext_with_context", C, g, R, M), n.applyFilters("i18n.gettext_with_context_" + v(M), C, g, R, M)) : C;
  }, N = (g, R, M, C) => {
    let Y = p(C, void 0, g, R, M);
    return n ? (Y = n.applyFilters("i18n.ngettext", Y, g, R, M, C), n.applyFilters("i18n.ngettext_" + v(C), Y, g, R, M, C)) : Y;
  }, I = (g, R, M, C, Y) => {
    let P = p(Y, C, g, R, M);
    return n ? (P = n.applyFilters("i18n.ngettext_with_context", P, g, R, M, C, Y), n.applyFilters("i18n.ngettext_with_context_" + v(Y), P, g, R, M, C, Y)) : P;
  }, x = () => h("ltr", "text direction") === "rtl", _ = (g, R, M) => {
    var C, Y;
    const P = R ? R + "" + g : g;
    let $ = !!((Y = (C = a.data) == null ? void 0 : C[M ?? "default"]) != null && Y[P]);
    return n && ($ = n.applyFilters("i18n.has_translation", $, g, R, M), $ = n.applyFilters("i18n.has_translation_" + v(M), $, g, R, M)), $;
  };
  if (n) {
    const g = (R) => {
      u1.test(R) && l();
    };
    n.addAction("hookAdded", "core/i18n", g), n.addAction("hookRemoved", "core/i18n", g);
  }
  return { getLocaleData: i, setLocaleData: c, addLocaleData: d, resetLocaleData: u, subscribe: o, __: b, _x: h, _n: N, _nx: I, isRTL: x, hasTranslation: _ };
};
function Rd(e) {
  return typeof e != "string" || e === "" ? (console.error("The namespace must be a non-empty string."), false) : /^[a-zA-Z][a-zA-Z0-9_.\-\/]*$/.test(e) ? true : (console.error("The namespace can only contain numbers, letters, dashes, periods, underscores and slashes."), false);
}
function Xi(e) {
  return typeof e != "string" || e === "" ? (console.error("The hook name must be a non-empty string."), false) : /^__/.test(e) ? (console.error("The hook name cannot begin with `__`."), false) : /^[a-zA-Z][a-zA-Z0-9_.-]*$/.test(e) ? true : (console.error("The hook name can only contain numbers, letters, dashes, periods and underscores."), false);
}
function bu(e, t) {
  return function(n, a, r, l = 10) {
    const o = e[t];
    if (!Xi(n) || !Rd(a)) return;
    if (typeof r != "function") {
      console.error("The hook callback must be a function.");
      return;
    }
    if (typeof l != "number") {
      console.error("If specified, the hook priority must be a number.");
      return;
    }
    const i = { callback: r, priority: l, namespace: a };
    if (o[n]) {
      const s = o[n].handlers;
      let c;
      for (c = s.length; c > 0 && !(l >= s[c - 1].priority); c--) ;
      c === s.length ? s[c] = i : s.splice(c, 0, i), o.__current.forEach((d) => {
        d.name === n && d.currentIndex >= c && d.currentIndex++;
      });
    } else o[n] = { handlers: [i], runs: 0 };
    n !== "hookAdded" && e.doAction("hookAdded", n, a, r, l);
  };
}
function Ml(e, t, n = false) {
  return function(a, r) {
    const l = e[t];
    if (!Xi(a) || !n && !Rd(r)) return;
    if (!l[a]) return 0;
    let o = 0;
    if (n) o = l[a].handlers.length, l[a] = { runs: l[a].runs, handlers: [] };
    else {
      const i = l[a].handlers;
      for (let s = i.length - 1; s >= 0; s--) i[s].namespace === r && (i.splice(s, 1), o++, l.__current.forEach((c) => {
        c.name === a && c.currentIndex >= s && c.currentIndex--;
      }));
    }
    return a !== "hookRemoved" && e.doAction("hookRemoved", a, r), o;
  };
}
function _u(e, t) {
  return function(n, a) {
    const r = e[t];
    return typeof a < "u" ? n in r && r[n].handlers.some((l) => l.namespace === a) : n in r;
  };
}
function xu(e, t, n = false) {
  return function(a, ...r) {
    const l = e[t];
    l[a] || (l[a] = { handlers: [], runs: 0 }), l[a].runs++;
    const o = l[a].handlers;
    if (!o || !o.length) return n ? r[0] : void 0;
    const i = { name: a, currentIndex: 0 };
    for (l.__current.push(i); i.currentIndex < o.length; ) {
      const s = o[i.currentIndex].callback.apply(null, r);
      n && (r[0] = s), i.currentIndex++;
    }
    if (l.__current.pop(), n) return r[0];
  };
}
function ku(e, t) {
  return function() {
    var n, a;
    const r = e[t];
    return (a = (n = r.__current[r.__current.length - 1]) == null ? void 0 : n.name) !== null && a !== void 0 ? a : null;
  };
}
function Su(e, t) {
  return function(n) {
    const a = e[t];
    return typeof n > "u" ? typeof a.__current[0] < "u" : a.__current[0] ? n === a.__current[0].name : false;
  };
}
function Cu(e, t) {
  return function(n) {
    const a = e[t];
    if (Xi(n)) return a[n] && a[n].runs ? a[n].runs : 0;
  };
}
class d1 {
  constructor() {
    this.actions = /* @__PURE__ */ Object.create(null), this.actions.__current = [], this.filters = /* @__PURE__ */ Object.create(null), this.filters.__current = [], this.addAction = bu(this, "actions"), this.addFilter = bu(this, "filters"), this.removeAction = Ml(this, "actions"), this.removeFilter = Ml(this, "filters"), this.hasAction = _u(this, "actions"), this.hasFilter = _u(this, "filters"), this.removeAllActions = Ml(this, "actions", true), this.removeAllFilters = Ml(this, "filters", true), this.doAction = xu(this, "actions"), this.applyFilters = xu(this, "filters", true), this.currentAction = ku(this, "actions"), this.currentFilter = ku(this, "filters"), this.doingAction = Su(this, "actions"), this.doingFilter = Su(this, "filters"), this.didAction = Cu(this, "actions"), this.didFilter = Cu(this, "filters");
  }
}
function p1() {
  return new d1();
}
const f1 = p1(), _t = c1(void 0, void 0, f1);
_t.getLocaleData.bind(_t);
_t.setLocaleData.bind(_t);
_t.resetLocaleData.bind(_t);
_t.subscribe.bind(_t);
const ke = _t.__.bind(_t);
_t._x.bind(_t);
_t._n.bind(_t);
_t._nx.bind(_t);
_t.isRTL.bind(_t);
_t.hasTranslation.bind(_t);
const v1 = { class: "wpuf-w-[calc(100%+40px)] wpuf-ml-[-20px] wpuf-px-[20px] wpuf-flex wpuf-mt-4 wpuf-justify-between wpuf-items-center wpuf-border-b-2 wpuf-border-gray-100 wpuf-pb-4" }, m1 = { class: "wpuf-flex wpuf-justify-start wpuf-items-center" }, h1 = { class: "wpuf-ml-2 wpuf-inline-flex wpuf-items-center wpuf-rounded-full wpuf-bg-green-100 wpuf-px-2 wpuf-py-1 wpuf-text-xs wpuf-font-medium wpuf-text-green-700 wpuf-ring-1 wpuf-ring-inset wpuf-ring-green-600/20" }, g1 = ["href"], w1 = { class: "wpuf-flex wpuf-justify-end wpuf-items-center wpuf-w-2/4" }, y1 = { class: "wpuf-border wpuf-border-gray-100 wpuf-mr-[16px] wpuf-canny-link wpuf-text-center wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-shadow-sm hover:wpuf-bg-slate-100 focus:wpuf-bg-slate-100", target: "_blank", href: "https://wpuf.canny.io/ideas" }, b1 = ["href"], _1 = { __name: "Header", setup(e) {
  const t = Hn("wpufSubscriptions"), n = t.assetUrl + "/images/wpuf-icon-circle.svg";
  return (a, r) => (T(), F("div", v1, [L("div", m1, [L("img", { src: n, alt: "WPUF Icon", class: "wpuf-w-12 wpuf-mr-4" }), L("h2", null, ge(f(ke)("WP User Frontend", "wp-user-frontend")), 1), L("span", h1, "v" + ge(f(t).version), 1), f(t).isProActive ? Z("", true) : (T(), F("a", { key: 0, href: f(t).upgradeUrl, target: "_blank", class: "wpuf-ml-4 wpuf-rounded-md wpuf-bg-amber-500 wpuf-px-3 wpuf-py-2 wpuf-text-sm font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-amber-600 hover:wpuf-text-white hover:wpuf-shadow-none active:wpuf-shadow-none focus:wpuf-bg-amber-600 focus:wpuf-text-white" }, [Ge(ge(f(ke)("Upgrade", "wp-user-frontend")) + "   ", 1), r[0] || (r[0] = L("span", { class: "dashicons dashicons-superhero-alt" }, null, -1))], 8, g1))]), L("div", w1, [r[2] || (r[2] = L("span", { id: "wpuf-headway-icon", class: "wpuf-border wpuf-border-gray-100 wpuf-mr-[16px] wpuf-rounded-full wpuf-p-1 wpuf-shadow-sm hover:wpuf-bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2" }, null, -1)), L("a", y1, "💡 " + ge(f(ke)("Submit Ideas", "wp-user-frontend")), 1), L("a", { href: f(t).supportUrl, target: "_blank", class: "wpuf-rounded-md wpuf-text-center wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 hover:wpuf-text-white focus:wpuf-bg-indigo-500 focus:wpuf-text-white" }, [Ge(ge(f(ke)("Support ", "wp-user-frontend")) + "    ", 1), r[1] || (r[1] = L("span", { class: "dashicons dashicons-businessman" }, null, -1))], 8, b1)])]));
} };
function x1(e) {
  const t = (n, a) => {
    const { headers: r = {} } = n;
    for (const l in r) if (l.toLowerCase() === "x-wp-nonce" && r[l] === t.nonce) return a(n);
    return a({ ...n, headers: { ...r, "X-WP-Nonce": t.nonce } });
  };
  return t.nonce = e, t;
}
const Ed = (e, t) => {
  let n = e.path, a, r;
  return typeof e.namespace == "string" && typeof e.endpoint == "string" && (a = e.namespace.replace(/^\/|\/$/g, ""), r = e.endpoint.replace(/^\//, ""), r ? n = a + "/" + r : n = a), delete e.namespace, delete e.endpoint, t({ ...e, path: n });
}, k1 = (e) => (t, n) => Ed(t, (a) => {
  let r = a.url, l = a.path, o;
  return typeof l == "string" && (o = e, e.indexOf("?") !== -1 && (l = l.replace("?", "&")), l = l.replace(/^\//, ""), typeof o == "string" && o.indexOf("?") !== -1 && (l = l.replace("?", "&")), r = o + l), n({ ...a, url: r });
});
function S1(e) {
  let t;
  try {
    t = new URL(e, "http://example.com").search.substring(1);
  } catch {
  }
  if (t) return t;
}
function Nd(e) {
  let t = "";
  const n = Object.entries(e);
  let a;
  for (; a = n.shift(); ) {
    let [r, l] = a;
    if (Array.isArray(l) || l && l.constructor === Object) {
      const o = Object.entries(l).reverse();
      for (const [i, s] of o) n.unshift([`${r}[${i}]`, s]);
    } else l !== void 0 && (l === null && (l = ""), t += "&" + [r, l].map(encodeURIComponent).join("="));
  }
  return t.substr(1);
}
function C1(e) {
  try {
    return decodeURIComponent(e);
  } catch {
    return e;
  }
}
function M1(e, t, n) {
  const a = t.length, r = a - 1;
  for (let l = 0; l < a; l++) {
    let o = t[l];
    !o && Array.isArray(e) && (o = e.length.toString()), o = ["__proto__", "constructor", "prototype"].includes(o) ? o.toUpperCase() : o;
    const i = !isNaN(Number(t[l + 1]));
    e[o] = l === r ? n : e[o] || (i ? [] : {}), Array.isArray(e[o]) && !i && (e[o] = { ...e[o] }), e = e[o];
  }
}
function po(e) {
  return (S1(e) || "").replace(/\+/g, "%20").split("&").reduce((t, n) => {
    const [a, r = ""] = n.split("=").filter(Boolean).map(C1);
    if (a) {
      const l = a.replace(/\]/g, "").split("[");
      M1(t, l, r);
    }
    return t;
  }, /* @__PURE__ */ Object.create(null));
}
function On(e = "", t) {
  if (!t || !Object.keys(t).length) return e;
  let n = e;
  const a = e.indexOf("?");
  return a !== -1 && (t = Object.assign(po(e), t), n = n.substr(0, a)), n + "?" + Nd(t);
}
function _i(e, t) {
  return po(e)[t];
}
function Mu(e, t) {
  return _i(e, t) !== void 0;
}
function Tu(e, ...t) {
  const n = e.indexOf("?");
  if (n === -1) return e;
  const a = po(e), r = e.substr(0, n);
  t.forEach((o) => delete a[o]);
  const l = Nd(a);
  return l ? r + "?" + l : r;
}
function Au(e) {
  const t = e.split("?"), n = t[1], a = t[0];
  return n ? a + "?" + n.split("&").map((r) => r.split("=")).map((r) => r.map(decodeURIComponent)).sort((r, l) => r[0].localeCompare(l[0])).map((r) => r.map(encodeURIComponent)).map((r) => r.join("=")).join("&") : a;
}
function T1(e) {
  const t = Object.fromEntries(Object.entries(e).map(([n, a]) => [Au(n), a]));
  return (n, a) => {
    const { parse: r = true } = n;
    let l = n.path;
    if (!l && n.url) {
      const { rest_route: s, ...c } = po(n.url);
      typeof s == "string" && (l = On(s, c));
    }
    if (typeof l != "string") return a(n);
    const o = n.method || "GET", i = Au(l);
    if (o === "GET" && t[i]) {
      const s = t[i];
      return delete t[i], Du(s, !!r);
    } else if (o === "OPTIONS" && t[o] && t[o][i]) {
      const s = t[o][i];
      return delete t[o][i], Du(s, !!r);
    }
    return a(n);
  };
}
function Du(e, t) {
  return Promise.resolve(t ? e.body : new window.Response(JSON.stringify(e.body), { status: 200, statusText: "OK", headers: e.headers }));
}
const A1 = ({ path: e, url: t, ...n }, a) => ({ ...n, url: t && On(t, a), path: e && On(e, a) }), Lu = (e) => e.json ? e.json() : Promise.reject(e), D1 = (e) => {
  if (!e) return {};
  const t = e.match(/<([^>]+)>; rel="next"/);
  return t ? { next: t[1] } : {};
}, Ou = (e) => {
  const { next: t } = D1(e.headers.get("link"));
  return t;
}, L1 = (e) => {
  const t = !!e.path && e.path.indexOf("per_page=-1") !== -1, n = !!e.url && e.url.indexOf("per_page=-1") !== -1;
  return t || n;
}, Id = async (e, t) => {
  if (e.parse === false || !L1(e)) return t(e);
  const n = await Bt({ ...A1(e, { per_page: 100 }), parse: false }), a = await Lu(n);
  if (!Array.isArray(a)) return a;
  let r = Ou(n);
  if (!r) return a;
  let l = [].concat(a);
  for (; r; ) {
    const o = await Bt({ ...e, path: void 0, url: r, parse: false }), i = await Lu(o);
    l = l.concat(i), r = Ou(o);
  }
  return l;
}, O1 = /* @__PURE__ */ new Set(["PATCH", "PUT", "DELETE"]), P1 = "GET", $1 = (e, t) => {
  const { method: n = P1 } = e;
  return O1.has(n.toUpperCase()) && (e = { ...e, headers: { ...e.headers, "X-HTTP-Method-Override": n, "Content-Type": "application/json" }, method: "POST" }), t(e);
}, R1 = (e, t) => (typeof e.url == "string" && !Mu(e.url, "_locale") && (e.url = On(e.url, { _locale: "user" })), typeof e.path == "string" && !Mu(e.path, "_locale") && (e.path = On(e.path, { _locale: "user" })), t(e)), E1 = (e, t = true) => t ? e.status === 204 ? null : e.json ? e.json() : Promise.reject(e) : e, N1 = (e) => {
  const t = { code: "invalid_json", message: ke("The response is not a valid JSON response.") };
  if (!e || !e.json) throw t;
  return e.json().catch(() => {
    throw t;
  });
}, Vd = (e, t = true) => Promise.resolve(E1(e, t)).catch((n) => Ji(n, t));
function Ji(e, t = true) {
  if (!t) throw e;
  return N1(e).then((n) => {
    const a = { code: "unknown_error", message: ke("An unknown error occurred.") };
    throw n || a;
  });
}
function I1(e) {
  const t = !!e.method && e.method === "POST";
  return (!!e.path && e.path.indexOf("/wp/v2/media") !== -1 || !!e.url && e.url.indexOf("/wp/v2/media") !== -1) && t;
}
const V1 = (e, t) => {
  if (!I1(e)) return t(e);
  let n = 0;
  const a = 5, r = (l) => (n++, t({ path: `/wp/v2/media/${l}/post-process`, method: "POST", data: { action: "create-image-subsizes" }, parse: false }).catch(() => n < a ? r(l) : (t({ path: `/wp/v2/media/${l}?force=true`, method: "DELETE" }), Promise.reject())));
  return t({ ...e, parse: false }).catch((l) => {
    const o = l.headers.get("x-wp-upload-attachment-id");
    return l.status >= 500 && l.status < 600 && o ? r(o).catch(() => e.parse !== false ? Promise.reject({ code: "post_process", message: ke("Media upload failed. If this is a photo or a large image, please scale it down and try again.") }) : Promise.reject(l)) : Ji(l, e.parse);
  }).then((l) => Vd(l, e.parse));
}, j1 = (e) => (t, n) => {
  if (typeof t.url == "string") {
    const a = _i(t.url, "wp_theme_preview");
    a === void 0 ? t.url = On(t.url, { wp_theme_preview: e }) : a === "" && (t.url = Tu(t.url, "wp_theme_preview"));
  }
  if (typeof t.path == "string") {
    const a = _i(t.path, "wp_theme_preview");
    a === void 0 ? t.path = On(t.path, { wp_theme_preview: e }) : a === "" && (t.path = Tu(t.path, "wp_theme_preview"));
  }
  return n(t);
}, B1 = { Accept: "application/json, */*;q=0.1" }, F1 = { credentials: "include" }, jd = [R1, Ed, $1, Id];
function Y1(e) {
  jd.unshift(e);
}
const Bd = (e) => {
  if (e.status >= 200 && e.status < 300) return e;
  throw e;
}, q1 = (e) => {
  const { url: t, path: n, data: a, parse: r = true, ...l } = e;
  let { body: o, headers: i } = e;
  return i = { ...B1, ...i }, a && (o = JSON.stringify(a), i["Content-Type"] = "application/json"), window.fetch(t || n || window.location.href, { ...F1, ...l, body: o, headers: i }).then((s) => Promise.resolve(s).then(Bd).catch((c) => Ji(c, r)).then((c) => Vd(c, r)), (s) => {
    throw s && s.name === "AbortError" ? s : { code: "fetch_error", message: ke("You are probably offline.") };
  });
};
let Fd = q1;
function z1(e) {
  Fd = e;
}
function Bt(e) {
  return jd.reduceRight((t, n) => (a) => n(a, t), Fd)(e).catch((t) => t.code !== "rest_cookie_invalid_nonce" ? Promise.reject(t) : window.fetch(Bt.nonceEndpoint).then(Bd).then((n) => n.text()).then((n) => (Bt.nonceMiddleware.nonce = n, Bt(e))));
}
Bt.use = Y1;
Bt.setFetchHandler = z1;
Bt.createNonceMiddleware = x1;
Bt.createPreloadingMiddleware = T1;
Bt.createRootURLMiddleware = k1;
Bt.fetchAllMiddleware = Id;
Bt.mediaUploadMiddleware = V1;
Bt.createThemePreviewMiddleware = j1;
const Yt = sl("subscription", { state: () => ({ subscriptionList: te([]), isUpdating: te(false), isSubscriptionLoading: te(false), isDirty: te(false), isUnsavedPopupOpen: te(false), currentSubscriptionStatus: te("all"), currentSubscriptionCopy: te(null), currentSubscription: te(null), errors: un({}), updateError: un({ status: false, message: "" }), allCount: te({}), taxonomyRestriction: te({}), currentPageNumber: te(1) }), getters: { fieldNames: () => {
  const e = wpufSubscriptions.fields, t = [];
  for (const n in e) if (e.hasOwnProperty(n)) {
    for (const a in e[n]) if (e[n].hasOwnProperty(a)) for (const r in e[n][a]) t.push(r);
  }
  return t;
}, fields: () => {
  const e = wpufSubscriptions.fields, t = [];
  for (const n in e) if (e.hasOwnProperty(n)) {
    for (const a in e[n]) if (e[n].hasOwnProperty(a)) for (const r in e[n][a]) t.push(e[n][a][r]);
  }
  return t;
} }, actions: { setCurrentSubscription(e) {
  this.currentSubscription = e;
}, setCurrentSubscriptionCopy() {
  this.currentSubscriptionCopy = this.subscription;
}, setBlankSubscription() {
  this.currentSubscription = {}, this.currentSubscription.meta_value = {};
  for (const e of this.fields) if (e.hasOwnProperty("type") && e.type === "inline") for (const t in e.fields) this.populateDefaultValue(e.fields[t]);
  else this.populateDefaultValue(e);
}, populateDefaultValue(e) {
  switch (e.db_type) {
    case "post":
      this.currentSubscription[e.db_key] = e.default;
      break;
    case "meta":
      this.currentSubscription.meta_value[e.db_key] = e.default;
      break;
    case "meta_serialized":
      let t = {};
      this.currentSubscription.meta_value.hasOwnProperty(e.db_key) && (t = this.currentSubscription.meta_value[e.db_key]), t[e.serialize_key] = e.default, this.currentSubscription.meta_value[e.db_key] = t;
      break;
  }
}, getValueFromField(e) {
  switch (e.type) {
    case "input-text":
    case "input-number":
    case "textarea":
    case "switcher":
    case "select":
      return document.querySelector("#" + e.id).value;
    case "time-date":
      return document.querySelector("#dp-input-" + e.id).value;
    default:
      return "";
  }
}, async updateSubscription() {
  if (this.currentSubscription === null) return false;
  this.isUpdating = true;
  let e = [];
  for (const [o, i] of Object.entries(this.taxonomyRestriction)) e = e.concat(i);
  const t = e.map((o) => parseInt(o)), n = [...new Set(t)];
  this.setMetaValue("_sub_allowed_term_ids", n);
  const a = this.currentSubscription;
  let r = "/wp-json/wpuf/v1/wpuf_subscription";
  a.ID && (r += "/" + a.ID);
  const l = { method: "POST", headers: { "Content-Type": "application/json", "X-WP-Nonce": wpufSubscriptions.nonce }, body: JSON.stringify({ subscription: a }) };
  return this.isDirty = false, fetch(r, l).then((o) => o.json()).catch((o) => {
    this.setError("fetch", "An error occurred while updating the subscription.");
  }).finally(() => {
    this.isUpdating = false;
  });
}, modifyCurrentSubscription(e, t, n = null) {
  if (this.currentSubscription === null) {
    this.setBlankSubscription();
    return;
  }
  if (this.isDirty = true, n === null) {
    this.currentSubscription.hasOwnProperty(e) ? this.currentSubscription[e] = t : this.setMetaValue(e, t);
    return;
  }
  this.currentSubscription.meta_value.hasOwnProperty(e) && (this.currentSubscription.meta_value[e][n] = t);
}, getMetaValue(e) {
  return this.currentSubscription.meta_value.hasOwnProperty(e) ? this.currentSubscription.meta_value[e] : "";
}, setMetaValue(e, t) {
  this.currentSubscription.meta_value[e] = t, this.isDirty = true;
}, getSerializedMetaValue(e, t) {
  if (!this.currentSubscription.meta_value.hasOwnProperty(e)) return "";
  const n = this.getMetaValue(e);
  return n.hasOwnProperty(t) ? n[t] : "";
}, setError(e, t) {
  this.errors[e] = { status: true, message: t };
}, resetErrors() {
  this.errors = {};
}, hasError() {
  for (const e in this.errors) if (this.errors[e]) return true;
  return false;
}, validateQuickEdit() {
  const e = this.currentSubscription.post_title;
  e === "" && this.setError("planName", ke("This field is required", "wp-user-frontend")), e.includes("#") && this.setError("planName", ke("# is not supported in plan name", "wp-user-frontend"));
}, validateEdit() {
  const e = this.currentSubscription, t = wpufSubscriptions.fields;
  for (const n in t) if (t.hasOwnProperty(n)) {
    for (const a in t[n]) if (t[n].hasOwnProperty(a)) for (const r in t[n][a]) {
      const l = t[n][a][r];
      let o = "";
      switch (l.db_type) {
        case "meta":
          o = e.meta_value[l.db_key];
          break;
        case "meta_serialized":
          o = e.meta_value[l.db_key];
          break;
        case "post":
          o = e[l.db_key];
          break;
        default:
          o = "";
          break;
      }
      l.id === "plan-name" && o.includes("#") && this.setError(r, ke("# is not supported in plan name", "wp-user-frontend")), l.is_required && o === "" && this.setError(r, ke(l.label + " is required", "wp-user-frontend"));
    }
  }
}, validateFields(e = "update") {
  switch (this.resetErrors(), e) {
    case "quickEdit":
      this.validateQuickEdit();
      break;
    default:
      this.validateEdit();
      break;
  }
  return !this.hasError();
}, deleteSubscription(e) {
  const t = { method: "DELETE", headers: { "Content-Type": "application/json", "X-WP-Nonce": wpufSubscriptions.nonce } };
  return fetch("/wp-json/wpuf/v1/wpuf_subscription/" + e, t).then((n) => n.json()).catch((n) => {
    console.log(n);
  });
}, changeSubscriptionStatus(e) {
  return e.edit_single_row = true, this.setCurrentSubscription(e), this.updateSubscription();
}, async setSubscriptionsByStatus(e, t = 0) {
  this.isSubscriptionLoading = true;
  const n = { per_page: wpufSubscriptions.perPage, offset: t, post_status: e };
  return Bt({ path: On("/wp-json/wpuf/v1/wpuf_subscription", n), method: "GET", headers: { "Content-Type": "application/json", "X-WP-Nonce": wpufSubscriptions.nonce } }).then((a) => (a.success && (this.currentSubscriptionStatus = e, this.subscriptionList = a.subscriptions), a)).catch((a) => {
    console.log(a);
  }).finally(() => {
    this.isSubscriptionLoading = false;
  });
}, async getSubscriptionCount(e = "all") {
  let t = "/wp-json/wpuf/v1/wpuf_subscription/count";
  return e !== "all" && (t += "/" + e), Bt({ path: On(t), method: "GET", headers: { "X-WP-Nonce": wpufSubscriptions.nonce } }).then((n) => {
    n.success && (this.allCount = n.count);
  }).catch((n) => {
    console.log(n);
  });
}, getReadableBillingAmount(e, t = false) {
  if (this.isRecurring(e)) {
    const n = e.meta_value.cycle_period === "" ? ke("day", "wp-user-frontend") : e.meta_value.cycle_period, a = parseInt(e.meta_value._billing_cycle_number) === 0 || parseInt(e.meta_value._billing_cycle_number) === 1 ? "" : " " + e.meta_value._billing_cycle_number + " ";
    return t ? wpufSubscriptions.currencySymbol + e.meta_value.billing_amount + ' <span class="wpuf-text-sm wpuf-text-gray-500">per ' + a + " " + n + "(s)</span>" : wpufSubscriptions.currencySymbol + e.meta_value.billing_amount + " per " + a + " " + n + "(s)";
  } else return parseInt(e.meta_value.billing_amount) === 0 || e.meta_value.billing_amount === "" ? ke("Free", "wp-user-frontend") : wpufSubscriptions.currencySymbol + e.meta_value.billing_amount;
}, isRecurring(e) {
  return e.meta_value.recurring_pay === "on" || e.meta_value.recurring_pay === "yes";
} } }), H1 = { class: "wpuf-flex wpuf-flex-col wpuf-pr-[48px]" }, K1 = { class: "wpuf-space-y-2 wpuf-text-lg" }, Z1 = ["onClick"], W1 = { __name: "SidebarMenu", setup(e) {
  const t = Yt(), n = [{ all: ke("All Subscriptions", "wp-user-frontend") }, { publish: ke("Published", "wp-user-frontend") }, { draft: ke("Drafts", "wp-user-frontend") }, { trash: ke("Trash", "wp-user-frontend") }];
  return n.map((a) => {
    const r = Object.keys(a)[0];
    a[r];
  }), (a, r) => (T(), F("div", { class: pe(f(t).isUnsavedPopupOpen ? "wpuf-blur" : "") }, [L("div", H1, [L("ul", K1, [(T(), F(Ce, null, Ve(n, (l) => L("li", { key: Object.keys(l)[0], onClick: (o) => a.$emit("checkIsDirty", Object.keys(l)[0]), class: pe([f(t).currentSubscriptionStatus === Object.keys(l)[0] ? "wpuf-bg-gray-50 wpuf-text-indigo-600" : "", "wpuf-justify-between wpuf-text-gray-700 hover:wpuf-text-indigo-600 hover:wpuf-bg-gray-50 group wpuf-flex wpuf-gap-x-3 wpuf-rounded-md wpuf-py-2 wpuf-px-[20px] wpuf-text-sm wpuf-leading-6 hover:wpuf-cursor-pointer"]) }, [Ge(ge(l[Object.keys(l)[0]]) + " ", 1), f(t).allCount[Object.keys(l)[0]] > 0 ? (T(), F("span", { key: 0, class: pe([f(t).currentSubscriptionStatus === Object.keys(l)[0] ? "wpuf-border-indigo-600" : "", "wpuf-text-sm wpuf-w-fit wpuf-px-2.5 wpuf-py-1 wpuf-rounded-full wpuf-w-max wpuf-h-max wpuf-border"]) }, ge(f(t).allCount[Object.keys(l)[0]]), 3)) : Z("", true)], 10, Z1)), 64))])])], 2));
} }, U1 = { class: "wpuf-mt-4 wpuf-border wpuf-border-gray-200" }, G1 = { class: "wpuf-mx-auto wpuf-grid bg-gray-900/5 wpuf-grid-cols-4 wpuf-border-b-2 wpuf-border-dashed wpuf-bg-white wpuf-p-2" }, Q1 = ["title"], X1 = { class: "wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500" }, J1 = { class: "wpuf-w-full wpuf-flex-none wpuf-text-2xl wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900" }, em = { class: "wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-px-4 wpuf-py-2" }, tm = { class: "wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500" }, nm = ["innerHTML"], am = { key: 0, class: "wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-px-4 wpuf-py-5" }, rm = { class: "wpuf-text-sm wpuf-italic wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500 wpuf-flex wpuf-items-center wpuf-justify-center" }, lm = { class: "wpuf-mx-auto wpuf-grid wpuf-grid-cols-1 bg-gray-900/5 wpuf-bg-white wpuf-p-2" }, om = { class: "wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-bg-white wpuf-px-4 wpuf-py-2" }, im = { class: "wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500" }, sm = { class: "wpuf-flex wpuf-items-center wpuf-w-full wpuf-flex-none wpuf-text-2xl wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900" }, um = { __name: "InfoCard", setup(e) {
  const t = Yt(), n = t.currentSubscription, a = J(() => n.meta_value.recurring_pay === "on" || n.meta_value.recurring_pay === "yes");
  return J(() => {
    if (parseFloat(n.meta_value.billing_amount) === 0) return ke("Free", "wp-user-frontend");
    if (a.value) {
      const r = n.meta_value.cycle_period === "" ? ke("day", "wp-user-frontend") : n.meta_value.cycle_period, l = n.meta_value._billing_cycle_number !== "0" ? " " + n.meta_value._billing_cycle_number + " " : "";
      return wpufSubscriptions.currencySymbol + n.meta_value.billing_amount + ' <span class="wpuf-text-sm wpuf-text-gray-500">per ' + l + " " + r + "(s)</span>";
    }
    return wpufSubscriptions.currencySymbol + n.meta_value.billing_amount;
  }), (r, l) => (T(), F("div", U1, [L("dl", G1, [L("div", { class: "wpuf-flex wpuf-col-span-2 wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-py-2 wpuf-px-6", title: "id: " + f(n).ID }, [L("dt", X1, ge(f(ke)("Plan", "wp-user-frontend")), 1), L("dd", J1, ge(f(n).post_title), 1)], 8, Q1), L("div", em, [L("dt", tm, ge(f(ke)("Payment", "wp-user-frontend")), 1), L("dd", { class: "wpuf-w-full wpuf-flex-none wpuf-text-2xl wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900", innerHTML: f(t).getReadableBillingAmount(f(n), true) }, null, 8, nm)]), a.value ? (T(), F("div", am, [L("dt", rm, [l[0] || (l[0] = L("svg", { width: "24", height: "24", viewBox: "0 0 24 24", fill: "none", xmlns: "http://www.w3.org/2000/svg" }, [L("path", { d: "M20 19C20 19.5523 20.4477 20 21 20C21.5523 20 22 19.5523 22 19L20 19ZM21 15.375L22 15.375L22 14.375H21V15.375ZM12 21L12 22L12 21ZM4.06195 13.0013C3.99361 12.4532 3.49394 12.0644 2.9459 12.1327C2.39786 12.201 2.00898 12.7007 2.07732 13.2488L4.06195 13.0013ZM20.3458 15.375L20.3458 14.375L20.3458 14.375L20.3458 15.375ZM17.375 14.375C16.8227 14.375 16.375 14.8227 16.375 15.375C16.375 15.9273 16.8227 16.375 17.375 16.375L17.375 14.375ZM4.00001 5.00002C4.00001 4.44773 3.55229 4.00002 3.00001 4.00002C2.44772 4.00002 2.00001 4.44773 2.00001 5.00002L4.00001 5.00002ZM3.00001 8.62502L2.00001 8.62502L2.00001 9.62502H3.00001V8.62502ZM3.65421 8.62502L3.65421 9.62502L3.65421 9.62502L3.65421 8.62502ZM12 3.00002L12 2.00002L12 3.00002ZM6.62501 9.62502C7.17729 9.62502 7.62501 9.1773 7.62501 8.62502C7.62501 8.07273 7.17729 7.62502 6.62501 7.62502L6.62501 9.62502ZM19.9381 10.9988C20.0064 11.5468 20.5061 11.9357 21.0541 11.8673C21.6022 11.799 21.991 11.2993 21.9227 10.7513L19.9381 10.9988ZM12.8552 9.58595C13.1788 10.0335 13.804 10.134 14.2515 9.81034C14.699 9.48673 14.7995 8.86159 14.4759 8.41404L12.8552 9.58595ZM12.5 7C12.5 6.44771 12.0523 6 11.5 6C10.9477 6 10.5 6.44771 10.5 7H12.5ZM10.5 17C10.5 17.5523 10.9477 18 11.5 18C12.0523 18 12.5 17.5523 12.5 17L10.5 17ZM10.1448 14.414C9.82121 13.9665 9.19606 13.866 8.74852 14.1896C8.30098 14.5133 8.20051 15.1384 8.52412 15.5859L10.1448 14.414ZM22 19L22 15.375L20 15.375L20 19L22 19ZM12 20C7.92115 20 4.55392 16.9466 4.06195 13.0013L2.07732 13.2488C2.69257 18.1827 6.89973 22 12 22L12 20ZM19.4189 14.9998C18.2313 17.9335 15.3558 20 12 20L12 22C16.1983 22 19.79 19.4132 21.2727 15.7502L19.4189 14.9998ZM21 14.375H20.3458V16.375H21V14.375ZM20.3458 14.375L17.375 14.375L17.375 16.375L20.3458 16.375L20.3458 14.375ZM2.00001 5.00002L2.00001 8.62502L4.00001 8.62502L4.00001 5.00002L2.00001 5.00002ZM4.58115 9.00023C5.76867 6.06656 8.6442 4.00002 12 4.00002L12 2.00002C7.80171 2.00002 4.21 4.58686 2.72728 8.2498L4.58115 9.00023ZM3.00001 9.62502H3.65421V7.62502H3.00001V9.62502ZM3.65421 9.62502L6.62501 9.62502L6.62501 7.62502L3.65421 7.62502L3.65421 9.62502ZM12 4.00002C16.0789 4.00001 19.4461 7.05347 19.9381 10.9988L21.9227 10.7513C21.3074 5.81736 17.1003 2.00001 12 2.00002L12 4.00002ZM11.5 11C10.4518 11 10 10.3556 10 10H8C8 11.8535 9.78676 13 11.5 13V11ZM10 10C10 9.64441 10.4518 9 11.5 9V7C9.78676 7 8 8.14644 8 10H10ZM11.5 9C12.1534 9 12.6379 9.28548 12.8552 9.58595L14.4759 8.41404C13.8286 7.51891 12.6973 7 11.5 7V9ZM11.5 13C12.5482 13 13 13.6444 13 14H15C15 12.1464 13.2132 11 11.5 11V13ZM10.5 7V8H12.5V7H10.5ZM10.5 16L10.5 17L12.5 17L12.5 16L10.5 16ZM11.5 15C10.8466 15 10.3621 14.7145 10.1448 14.414L8.52412 15.5859C9.17138 16.4811 10.3027 17 11.5 17L11.5 15ZM13 14C13 14.3556 12.5482 15 11.5 15V17C13.2132 17 15 15.8535 15 14H13Z", fill: "rgb(107 114 128)" })], -1)), Ge("   " + ge(f(ke)("Recurring", "wp-user-frontend")), 1)])])) : Z("", true)]), L("dl", lm, [L("div", om, [L("dt", im, ge(f(ke)("Subscribers", "wp-user-frontend")), 1), L("dd", sm, [l[1] || (l[1] = L("svg", { width: "24", height: "24", viewBox: "0 0 24 24", fill: "none", xmlns: "http://www.w3.org/2000/svg" }, [L("path", { d: "M12 10.8C13.9882 10.8 15.6 9.18822 15.6 7.2C15.6 5.21177 13.9882 3.6 12 3.6C10.0118 3.6 8.4 5.21177 8.4 7.2C8.4 9.18822 10.0118 10.8 12 10.8Z", fill: "#0F172A" }), L("path", { d: "M3.6 21.6C3.6 16.9608 7.36081 13.2 12 13.2C16.6392 13.2 20.4 16.9608 20.4 21.6H3.6Z", fill: "#0F172A" })], -1)), Ge("   " + ge(f(n).subscribers), 1)])])])]));
} };
function Te(e) {
  const t = Object.prototype.toString.call(e);
  return e instanceof Date || typeof e == "object" && t === "[object Date]" ? new e.constructor(+e) : typeof e == "number" || t === "[object Number]" || typeof e == "string" || t === "[object String]" ? new Date(e) : /* @__PURE__ */ new Date(NaN);
}
function Ke(e, t) {
  return e instanceof Date ? new e.constructor(t) : new Date(t);
}
function pn(e, t) {
  const n = Te(e);
  return isNaN(t) ? Ke(e, NaN) : (t && n.setDate(n.getDate() + t), n);
}
function hn(e, t) {
  const n = Te(e);
  if (isNaN(t)) return Ke(e, NaN);
  if (!t) return n;
  const a = n.getDate(), r = Ke(e, n.getTime());
  r.setMonth(n.getMonth() + t + 1, 0);
  const l = r.getDate();
  return a >= l ? r : (n.setFullYear(r.getFullYear(), r.getMonth(), a), n);
}
function Yd(e, t) {
  const { years: n = 0, months: a = 0, weeks: r = 0, days: l = 0, hours: o = 0, minutes: i = 0, seconds: s = 0 } = t, c = Te(e), d = a || n ? hn(c, a + n * 12) : c, u = l || r ? pn(d, l + r * 7) : d, p = i + o * 60, v = (s + p * 60) * 1e3;
  return Ke(e, u.getTime() + v);
}
function cm(e, t) {
  const n = +Te(e);
  return Ke(e, n + t);
}
const qd = 6048e5, dm = 864e5, pm = 6e4, zd = 36e5, fm = 1e3;
function vm(e, t) {
  return cm(e, t * zd);
}
let mm = {};
function Ka() {
  return mm;
}
function bn(e, t) {
  var n, a, r, l;
  const o = Ka(), i = (t == null ? void 0 : t.weekStartsOn) ?? ((a = (n = t == null ? void 0 : t.locale) == null ? void 0 : n.options) == null ? void 0 : a.weekStartsOn) ?? o.weekStartsOn ?? ((l = (r = o.locale) == null ? void 0 : r.options) == null ? void 0 : l.weekStartsOn) ?? 0, s = Te(e), c = s.getDay(), d = (c < i ? 7 : 0) + c - i;
  return s.setDate(s.getDate() - d), s.setHours(0, 0, 0, 0), s;
}
function ur(e) {
  return bn(e, { weekStartsOn: 1 });
}
function Hd(e) {
  const t = Te(e), n = t.getFullYear(), a = Ke(e, 0);
  a.setFullYear(n + 1, 0, 4), a.setHours(0, 0, 0, 0);
  const r = ur(a), l = Ke(e, 0);
  l.setFullYear(n, 0, 4), l.setHours(0, 0, 0, 0);
  const o = ur(l);
  return t.getTime() >= r.getTime() ? n + 1 : t.getTime() >= o.getTime() ? n : n - 1;
}
function Pu(e) {
  const t = Te(e);
  return t.setHours(0, 0, 0, 0), t;
}
function zl(e) {
  const t = Te(e), n = new Date(Date.UTC(t.getFullYear(), t.getMonth(), t.getDate(), t.getHours(), t.getMinutes(), t.getSeconds(), t.getMilliseconds()));
  return n.setUTCFullYear(t.getFullYear()), +e - +n;
}
function Kd(e, t) {
  const n = Pu(e), a = Pu(t), r = +n - zl(n), l = +a - zl(a);
  return Math.round((r - l) / dm);
}
function hm(e) {
  const t = Hd(e), n = Ke(e, 0);
  return n.setFullYear(t, 0, 4), n.setHours(0, 0, 0, 0), ur(n);
}
function gm(e, t) {
  const n = t * 3;
  return hn(e, n);
}
function es(e, t) {
  return hn(e, t * 12);
}
function $u(e, t) {
  const n = Te(e), a = Te(t), r = n.getTime() - a.getTime();
  return r < 0 ? -1 : r > 0 ? 1 : r;
}
function Zd(e) {
  return e instanceof Date || typeof e == "object" && Object.prototype.toString.call(e) === "[object Date]";
}
function Yr(e) {
  if (!Zd(e) && typeof e != "number") return false;
  const t = Te(e);
  return !isNaN(Number(t));
}
function Ru(e) {
  const t = Te(e);
  return Math.trunc(t.getMonth() / 3) + 1;
}
function wm(e, t) {
  const n = Te(e), a = Te(t);
  return n.getFullYear() - a.getFullYear();
}
function ym(e, t) {
  const n = Te(e), a = Te(t), r = $u(n, a), l = Math.abs(wm(n, a));
  n.setFullYear(1584), a.setFullYear(1584);
  const o = $u(n, a) === -r, i = r * (l - +o);
  return i === 0 ? 0 : i;
}
function Wd(e, t) {
  const n = Te(e.start), a = Te(e.end);
  let r = +n > +a;
  const l = r ? +n : +a, o = r ? a : n;
  o.setHours(0, 0, 0, 0);
  let i = 1;
  const s = [];
  for (; +o <= l; ) s.push(Te(o)), o.setDate(o.getDate() + i), o.setHours(0, 0, 0, 0);
  return r ? s.reverse() : s;
}
function rr(e) {
  const t = Te(e), n = t.getMonth(), a = n - n % 3;
  return t.setMonth(a, 1), t.setHours(0, 0, 0, 0), t;
}
function bm(e, t) {
  const n = Te(e.start), a = Te(e.end);
  let r = +n > +a;
  const l = r ? +rr(n) : +rr(a);
  let o = rr(r ? a : n), i = 1;
  const s = [];
  for (; +o <= l; ) s.push(Te(o)), o = gm(o, i);
  return r ? s.reverse() : s;
}
function _m(e) {
  const t = Te(e);
  return t.setDate(1), t.setHours(0, 0, 0, 0), t;
}
function Ud(e) {
  const t = Te(e), n = t.getFullYear();
  return t.setFullYear(n + 1, 0, 0), t.setHours(23, 59, 59, 999), t;
}
function Jr(e) {
  const t = Te(e), n = Ke(e, 0);
  return n.setFullYear(t.getFullYear(), 0, 1), n.setHours(0, 0, 0, 0), n;
}
function Gd(e, t) {
  var n, a, r, l;
  const o = Ka(), i = (t == null ? void 0 : t.weekStartsOn) ?? ((a = (n = t == null ? void 0 : t.locale) == null ? void 0 : n.options) == null ? void 0 : a.weekStartsOn) ?? o.weekStartsOn ?? ((l = (r = o.locale) == null ? void 0 : r.options) == null ? void 0 : l.weekStartsOn) ?? 0, s = Te(e), c = s.getDay(), d = (c < i ? -7 : 0) + 6 - (c - i);
  return s.setDate(s.getDate() + d), s.setHours(23, 59, 59, 999), s;
}
function Eu(e) {
  const t = Te(e), n = t.getMonth(), a = n - n % 3 + 3;
  return t.setMonth(a, 0), t.setHours(23, 59, 59, 999), t;
}
const xm = { lessThanXSeconds: { one: "less than a second", other: "less than {{count}} seconds" }, xSeconds: { one: "1 second", other: "{{count}} seconds" }, halfAMinute: "half a minute", lessThanXMinutes: { one: "less than a minute", other: "less than {{count}} minutes" }, xMinutes: { one: "1 minute", other: "{{count}} minutes" }, aboutXHours: { one: "about 1 hour", other: "about {{count}} hours" }, xHours: { one: "1 hour", other: "{{count}} hours" }, xDays: { one: "1 day", other: "{{count}} days" }, aboutXWeeks: { one: "about 1 week", other: "about {{count}} weeks" }, xWeeks: { one: "1 week", other: "{{count}} weeks" }, aboutXMonths: { one: "about 1 month", other: "about {{count}} months" }, xMonths: { one: "1 month", other: "{{count}} months" }, aboutXYears: { one: "about 1 year", other: "about {{count}} years" }, xYears: { one: "1 year", other: "{{count}} years" }, overXYears: { one: "over 1 year", other: "over {{count}} years" }, almostXYears: { one: "almost 1 year", other: "almost {{count}} years" } }, km = (e, t, n) => {
  let a;
  const r = xm[e];
  return typeof r == "string" ? a = r : t === 1 ? a = r.one : a = r.other.replace("{{count}}", t.toString()), n != null && n.addSuffix ? n.comparison && n.comparison > 0 ? "in " + a : a + " ago" : a;
};
function Yo(e) {
  return (t = {}) => {
    const n = t.width ? String(t.width) : e.defaultWidth;
    return e.formats[n] || e.formats[e.defaultWidth];
  };
}
const Sm = { full: "EEEE, MMMM do, y", long: "MMMM do, y", medium: "MMM d, y", short: "MM/dd/yyyy" }, Cm = { full: "h:mm:ss a zzzz", long: "h:mm:ss a z", medium: "h:mm:ss a", short: "h:mm a" }, Mm = { full: "{{date}} 'at' {{time}}", long: "{{date}} 'at' {{time}}", medium: "{{date}}, {{time}}", short: "{{date}}, {{time}}" }, Tm = { date: Yo({ formats: Sm, defaultWidth: "full" }), time: Yo({ formats: Cm, defaultWidth: "full" }), dateTime: Yo({ formats: Mm, defaultWidth: "full" }) }, Am = { lastWeek: "'last' eeee 'at' p", yesterday: "'yesterday at' p", today: "'today at' p", tomorrow: "'tomorrow at' p", nextWeek: "eeee 'at' p", other: "P" }, Dm = (e, t, n, a) => Am[e];
function Tr(e) {
  return (t, n) => {
    const a = n != null && n.context ? String(n.context) : "standalone";
    let r;
    if (a === "formatting" && e.formattingValues) {
      const o = e.defaultFormattingWidth || e.defaultWidth, i = n != null && n.width ? String(n.width) : o;
      r = e.formattingValues[i] || e.formattingValues[o];
    } else {
      const o = e.defaultWidth, i = n != null && n.width ? String(n.width) : e.defaultWidth;
      r = e.values[i] || e.values[o];
    }
    const l = e.argumentCallback ? e.argumentCallback(t) : t;
    return r[l];
  };
}
const Lm = { narrow: ["B", "A"], abbreviated: ["BC", "AD"], wide: ["Before Christ", "Anno Domini"] }, Om = { narrow: ["1", "2", "3", "4"], abbreviated: ["Q1", "Q2", "Q3", "Q4"], wide: ["1st quarter", "2nd quarter", "3rd quarter", "4th quarter"] }, Pm = { narrow: ["J", "F", "M", "A", "M", "J", "J", "A", "S", "O", "N", "D"], abbreviated: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"], wide: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"] }, $m = { narrow: ["S", "M", "T", "W", "T", "F", "S"], short: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"], abbreviated: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"], wide: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"] }, Rm = { narrow: { am: "a", pm: "p", midnight: "mi", noon: "n", morning: "morning", afternoon: "afternoon", evening: "evening", night: "night" }, abbreviated: { am: "AM", pm: "PM", midnight: "midnight", noon: "noon", morning: "morning", afternoon: "afternoon", evening: "evening", night: "night" }, wide: { am: "a.m.", pm: "p.m.", midnight: "midnight", noon: "noon", morning: "morning", afternoon: "afternoon", evening: "evening", night: "night" } }, Em = { narrow: { am: "a", pm: "p", midnight: "mi", noon: "n", morning: "in the morning", afternoon: "in the afternoon", evening: "in the evening", night: "at night" }, abbreviated: { am: "AM", pm: "PM", midnight: "midnight", noon: "noon", morning: "in the morning", afternoon: "in the afternoon", evening: "in the evening", night: "at night" }, wide: { am: "a.m.", pm: "p.m.", midnight: "midnight", noon: "noon", morning: "in the morning", afternoon: "in the afternoon", evening: "in the evening", night: "at night" } }, Nm = (e, t) => {
  const n = Number(e), a = n % 100;
  if (a > 20 || a < 10) switch (a % 10) {
    case 1:
      return n + "st";
    case 2:
      return n + "nd";
    case 3:
      return n + "rd";
  }
  return n + "th";
}, Im = { ordinalNumber: Nm, era: Tr({ values: Lm, defaultWidth: "wide" }), quarter: Tr({ values: Om, defaultWidth: "wide", argumentCallback: (e) => e - 1 }), month: Tr({ values: Pm, defaultWidth: "wide" }), day: Tr({ values: $m, defaultWidth: "wide" }), dayPeriod: Tr({ values: Rm, defaultWidth: "wide", formattingValues: Em, defaultFormattingWidth: "wide" }) };
function Ar(e) {
  return (t, n = {}) => {
    const a = n.width, r = a && e.matchPatterns[a] || e.matchPatterns[e.defaultMatchWidth], l = t.match(r);
    if (!l) return null;
    const o = l[0], i = a && e.parsePatterns[a] || e.parsePatterns[e.defaultParseWidth], s = Array.isArray(i) ? jm(i, (u) => u.test(o)) : Vm(i, (u) => u.test(o));
    let c;
    c = e.valueCallback ? e.valueCallback(s) : s, c = n.valueCallback ? n.valueCallback(c) : c;
    const d = t.slice(o.length);
    return { value: c, rest: d };
  };
}
function Vm(e, t) {
  for (const n in e) if (Object.prototype.hasOwnProperty.call(e, n) && t(e[n])) return n;
}
function jm(e, t) {
  for (let n = 0; n < e.length; n++) if (t(e[n])) return n;
}
function Bm(e) {
  return (t, n = {}) => {
    const a = t.match(e.matchPattern);
    if (!a) return null;
    const r = a[0], l = t.match(e.parsePattern);
    if (!l) return null;
    let o = e.valueCallback ? e.valueCallback(l[0]) : l[0];
    o = n.valueCallback ? n.valueCallback(o) : o;
    const i = t.slice(r.length);
    return { value: o, rest: i };
  };
}
const Fm = /^(\d+)(th|st|nd|rd)?/i, Ym = /\d+/i, qm = { narrow: /^(b|a)/i, abbreviated: /^(b\.?\s?c\.?|b\.?\s?c\.?\s?e\.?|a\.?\s?d\.?|c\.?\s?e\.?)/i, wide: /^(before christ|before common era|anno domini|common era)/i }, zm = { any: [/^b/i, /^(a|c)/i] }, Hm = { narrow: /^[1234]/i, abbreviated: /^q[1234]/i, wide: /^[1234](th|st|nd|rd)? quarter/i }, Km = { any: [/1/i, /2/i, /3/i, /4/i] }, Zm = { narrow: /^[jfmasond]/i, abbreviated: /^(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)/i, wide: /^(january|february|march|april|may|june|july|august|september|october|november|december)/i }, Wm = { narrow: [/^j/i, /^f/i, /^m/i, /^a/i, /^m/i, /^j/i, /^j/i, /^a/i, /^s/i, /^o/i, /^n/i, /^d/i], any: [/^ja/i, /^f/i, /^mar/i, /^ap/i, /^may/i, /^jun/i, /^jul/i, /^au/i, /^s/i, /^o/i, /^n/i, /^d/i] }, Um = { narrow: /^[smtwf]/i, short: /^(su|mo|tu|we|th|fr|sa)/i, abbreviated: /^(sun|mon|tue|wed|thu|fri|sat)/i, wide: /^(sunday|monday|tuesday|wednesday|thursday|friday|saturday)/i }, Gm = { narrow: [/^s/i, /^m/i, /^t/i, /^w/i, /^t/i, /^f/i, /^s/i], any: [/^su/i, /^m/i, /^tu/i, /^w/i, /^th/i, /^f/i, /^sa/i] }, Qm = { narrow: /^(a|p|mi|n|(in the|at) (morning|afternoon|evening|night))/i, any: /^([ap]\.?\s?m\.?|midnight|noon|(in the|at) (morning|afternoon|evening|night))/i }, Xm = { any: { am: /^a/i, pm: /^p/i, midnight: /^mi/i, noon: /^no/i, morning: /morning/i, afternoon: /afternoon/i, evening: /evening/i, night: /night/i } }, Jm = { ordinalNumber: Bm({ matchPattern: Fm, parsePattern: Ym, valueCallback: (e) => parseInt(e, 10) }), era: Ar({ matchPatterns: qm, defaultMatchWidth: "wide", parsePatterns: zm, defaultParseWidth: "any" }), quarter: Ar({ matchPatterns: Hm, defaultMatchWidth: "wide", parsePatterns: Km, defaultParseWidth: "any", valueCallback: (e) => e + 1 }), month: Ar({ matchPatterns: Zm, defaultMatchWidth: "wide", parsePatterns: Wm, defaultParseWidth: "any" }), day: Ar({ matchPatterns: Um, defaultMatchWidth: "wide", parsePatterns: Gm, defaultParseWidth: "any" }), dayPeriod: Ar({ matchPatterns: Qm, defaultMatchWidth: "any", parsePatterns: Xm, defaultParseWidth: "any" }) }, Qd = { code: "en-US", formatDistance: km, formatLong: Tm, formatRelative: Dm, localize: Im, match: Jm, options: { weekStartsOn: 0, firstWeekContainsDate: 1 } };
function eh(e) {
  const t = Te(e);
  return Kd(t, Jr(t)) + 1;
}
function ts(e) {
  const t = Te(e), n = +ur(t) - +hm(t);
  return Math.round(n / qd) + 1;
}
function ns(e, t) {
  var n, a, r, l;
  const o = Te(e), i = o.getFullYear(), s = Ka(), c = (t == null ? void 0 : t.firstWeekContainsDate) ?? ((a = (n = t == null ? void 0 : t.locale) == null ? void 0 : n.options) == null ? void 0 : a.firstWeekContainsDate) ?? s.firstWeekContainsDate ?? ((l = (r = s.locale) == null ? void 0 : r.options) == null ? void 0 : l.firstWeekContainsDate) ?? 1, d = Ke(e, 0);
  d.setFullYear(i + 1, 0, c), d.setHours(0, 0, 0, 0);
  const u = bn(d, t), p = Ke(e, 0);
  p.setFullYear(i, 0, c), p.setHours(0, 0, 0, 0);
  const v = bn(p, t);
  return o.getTime() >= u.getTime() ? i + 1 : o.getTime() >= v.getTime() ? i : i - 1;
}
function th(e, t) {
  var n, a, r, l;
  const o = Ka(), i = (t == null ? void 0 : t.firstWeekContainsDate) ?? ((a = (n = t == null ? void 0 : t.locale) == null ? void 0 : n.options) == null ? void 0 : a.firstWeekContainsDate) ?? o.firstWeekContainsDate ?? ((l = (r = o.locale) == null ? void 0 : r.options) == null ? void 0 : l.firstWeekContainsDate) ?? 1, s = ns(e, t), c = Ke(e, 0);
  return c.setFullYear(s, 0, i), c.setHours(0, 0, 0, 0), bn(c, t);
}
function as(e, t) {
  const n = Te(e), a = +bn(n, t) - +th(n, t);
  return Math.round(a / qd) + 1;
}
function Ue(e, t) {
  const n = e < 0 ? "-" : "", a = Math.abs(e).toString().padStart(t, "0");
  return n + a;
}
const ea = { y(e, t) {
  const n = e.getFullYear(), a = n > 0 ? n : 1 - n;
  return Ue(t === "yy" ? a % 100 : a, t.length);
}, M(e, t) {
  const n = e.getMonth();
  return t === "M" ? String(n + 1) : Ue(n + 1, 2);
}, d(e, t) {
  return Ue(e.getDate(), t.length);
}, a(e, t) {
  const n = e.getHours() / 12 >= 1 ? "pm" : "am";
  switch (t) {
    case "a":
    case "aa":
      return n.toUpperCase();
    case "aaa":
      return n;
    case "aaaaa":
      return n[0];
    case "aaaa":
    default:
      return n === "am" ? "a.m." : "p.m.";
  }
}, h(e, t) {
  return Ue(e.getHours() % 12 || 12, t.length);
}, H(e, t) {
  return Ue(e.getHours(), t.length);
}, m(e, t) {
  return Ue(e.getMinutes(), t.length);
}, s(e, t) {
  return Ue(e.getSeconds(), t.length);
}, S(e, t) {
  const n = t.length, a = e.getMilliseconds(), r = Math.trunc(a * Math.pow(10, n - 3));
  return Ue(r, t.length);
} }, Ua = { am: "am", pm: "pm", midnight: "midnight", noon: "noon", morning: "morning", afternoon: "afternoon", evening: "evening", night: "night" }, Nu = { G: function(e, t, n) {
  const a = e.getFullYear() > 0 ? 1 : 0;
  switch (t) {
    case "G":
    case "GG":
    case "GGG":
      return n.era(a, { width: "abbreviated" });
    case "GGGGG":
      return n.era(a, { width: "narrow" });
    case "GGGG":
    default:
      return n.era(a, { width: "wide" });
  }
}, y: function(e, t, n) {
  if (t === "yo") {
    const a = e.getFullYear(), r = a > 0 ? a : 1 - a;
    return n.ordinalNumber(r, { unit: "year" });
  }
  return ea.y(e, t);
}, Y: function(e, t, n, a) {
  const r = ns(e, a), l = r > 0 ? r : 1 - r;
  if (t === "YY") {
    const o = l % 100;
    return Ue(o, 2);
  }
  return t === "Yo" ? n.ordinalNumber(l, { unit: "year" }) : Ue(l, t.length);
}, R: function(e, t) {
  const n = Hd(e);
  return Ue(n, t.length);
}, u: function(e, t) {
  const n = e.getFullYear();
  return Ue(n, t.length);
}, Q: function(e, t, n) {
  const a = Math.ceil((e.getMonth() + 1) / 3);
  switch (t) {
    case "Q":
      return String(a);
    case "QQ":
      return Ue(a, 2);
    case "Qo":
      return n.ordinalNumber(a, { unit: "quarter" });
    case "QQQ":
      return n.quarter(a, { width: "abbreviated", context: "formatting" });
    case "QQQQQ":
      return n.quarter(a, { width: "narrow", context: "formatting" });
    case "QQQQ":
    default:
      return n.quarter(a, { width: "wide", context: "formatting" });
  }
}, q: function(e, t, n) {
  const a = Math.ceil((e.getMonth() + 1) / 3);
  switch (t) {
    case "q":
      return String(a);
    case "qq":
      return Ue(a, 2);
    case "qo":
      return n.ordinalNumber(a, { unit: "quarter" });
    case "qqq":
      return n.quarter(a, { width: "abbreviated", context: "standalone" });
    case "qqqqq":
      return n.quarter(a, { width: "narrow", context: "standalone" });
    case "qqqq":
    default:
      return n.quarter(a, { width: "wide", context: "standalone" });
  }
}, M: function(e, t, n) {
  const a = e.getMonth();
  switch (t) {
    case "M":
    case "MM":
      return ea.M(e, t);
    case "Mo":
      return n.ordinalNumber(a + 1, { unit: "month" });
    case "MMM":
      return n.month(a, { width: "abbreviated", context: "formatting" });
    case "MMMMM":
      return n.month(a, { width: "narrow", context: "formatting" });
    case "MMMM":
    default:
      return n.month(a, { width: "wide", context: "formatting" });
  }
}, L: function(e, t, n) {
  const a = e.getMonth();
  switch (t) {
    case "L":
      return String(a + 1);
    case "LL":
      return Ue(a + 1, 2);
    case "Lo":
      return n.ordinalNumber(a + 1, { unit: "month" });
    case "LLL":
      return n.month(a, { width: "abbreviated", context: "standalone" });
    case "LLLLL":
      return n.month(a, { width: "narrow", context: "standalone" });
    case "LLLL":
    default:
      return n.month(a, { width: "wide", context: "standalone" });
  }
}, w: function(e, t, n, a) {
  const r = as(e, a);
  return t === "wo" ? n.ordinalNumber(r, { unit: "week" }) : Ue(r, t.length);
}, I: function(e, t, n) {
  const a = ts(e);
  return t === "Io" ? n.ordinalNumber(a, { unit: "week" }) : Ue(a, t.length);
}, d: function(e, t, n) {
  return t === "do" ? n.ordinalNumber(e.getDate(), { unit: "date" }) : ea.d(e, t);
}, D: function(e, t, n) {
  const a = eh(e);
  return t === "Do" ? n.ordinalNumber(a, { unit: "dayOfYear" }) : Ue(a, t.length);
}, E: function(e, t, n) {
  const a = e.getDay();
  switch (t) {
    case "E":
    case "EE":
    case "EEE":
      return n.day(a, { width: "abbreviated", context: "formatting" });
    case "EEEEE":
      return n.day(a, { width: "narrow", context: "formatting" });
    case "EEEEEE":
      return n.day(a, { width: "short", context: "formatting" });
    case "EEEE":
    default:
      return n.day(a, { width: "wide", context: "formatting" });
  }
}, e: function(e, t, n, a) {
  const r = e.getDay(), l = (r - a.weekStartsOn + 8) % 7 || 7;
  switch (t) {
    case "e":
      return String(l);
    case "ee":
      return Ue(l, 2);
    case "eo":
      return n.ordinalNumber(l, { unit: "day" });
    case "eee":
      return n.day(r, { width: "abbreviated", context: "formatting" });
    case "eeeee":
      return n.day(r, { width: "narrow", context: "formatting" });
    case "eeeeee":
      return n.day(r, { width: "short", context: "formatting" });
    case "eeee":
    default:
      return n.day(r, { width: "wide", context: "formatting" });
  }
}, c: function(e, t, n, a) {
  const r = e.getDay(), l = (r - a.weekStartsOn + 8) % 7 || 7;
  switch (t) {
    case "c":
      return String(l);
    case "cc":
      return Ue(l, t.length);
    case "co":
      return n.ordinalNumber(l, { unit: "day" });
    case "ccc":
      return n.day(r, { width: "abbreviated", context: "standalone" });
    case "ccccc":
      return n.day(r, { width: "narrow", context: "standalone" });
    case "cccccc":
      return n.day(r, { width: "short", context: "standalone" });
    case "cccc":
    default:
      return n.day(r, { width: "wide", context: "standalone" });
  }
}, i: function(e, t, n) {
  const a = e.getDay(), r = a === 0 ? 7 : a;
  switch (t) {
    case "i":
      return String(r);
    case "ii":
      return Ue(r, t.length);
    case "io":
      return n.ordinalNumber(r, { unit: "day" });
    case "iii":
      return n.day(a, { width: "abbreviated", context: "formatting" });
    case "iiiii":
      return n.day(a, { width: "narrow", context: "formatting" });
    case "iiiiii":
      return n.day(a, { width: "short", context: "formatting" });
    case "iiii":
    default:
      return n.day(a, { width: "wide", context: "formatting" });
  }
}, a: function(e, t, n) {
  const a = e.getHours() / 12 >= 1 ? "pm" : "am";
  switch (t) {
    case "a":
    case "aa":
      return n.dayPeriod(a, { width: "abbreviated", context: "formatting" });
    case "aaa":
      return n.dayPeriod(a, { width: "abbreviated", context: "formatting" }).toLowerCase();
    case "aaaaa":
      return n.dayPeriod(a, { width: "narrow", context: "formatting" });
    case "aaaa":
    default:
      return n.dayPeriod(a, { width: "wide", context: "formatting" });
  }
}, b: function(e, t, n) {
  const a = e.getHours();
  let r;
  switch (a === 12 ? r = Ua.noon : a === 0 ? r = Ua.midnight : r = a / 12 >= 1 ? "pm" : "am", t) {
    case "b":
    case "bb":
      return n.dayPeriod(r, { width: "abbreviated", context: "formatting" });
    case "bbb":
      return n.dayPeriod(r, { width: "abbreviated", context: "formatting" }).toLowerCase();
    case "bbbbb":
      return n.dayPeriod(r, { width: "narrow", context: "formatting" });
    case "bbbb":
    default:
      return n.dayPeriod(r, { width: "wide", context: "formatting" });
  }
}, B: function(e, t, n) {
  const a = e.getHours();
  let r;
  switch (a >= 17 ? r = Ua.evening : a >= 12 ? r = Ua.afternoon : a >= 4 ? r = Ua.morning : r = Ua.night, t) {
    case "B":
    case "BB":
    case "BBB":
      return n.dayPeriod(r, { width: "abbreviated", context: "formatting" });
    case "BBBBB":
      return n.dayPeriod(r, { width: "narrow", context: "formatting" });
    case "BBBB":
    default:
      return n.dayPeriod(r, { width: "wide", context: "formatting" });
  }
}, h: function(e, t, n) {
  if (t === "ho") {
    let a = e.getHours() % 12;
    return a === 0 && (a = 12), n.ordinalNumber(a, { unit: "hour" });
  }
  return ea.h(e, t);
}, H: function(e, t, n) {
  return t === "Ho" ? n.ordinalNumber(e.getHours(), { unit: "hour" }) : ea.H(e, t);
}, K: function(e, t, n) {
  const a = e.getHours() % 12;
  return t === "Ko" ? n.ordinalNumber(a, { unit: "hour" }) : Ue(a, t.length);
}, k: function(e, t, n) {
  let a = e.getHours();
  return a === 0 && (a = 24), t === "ko" ? n.ordinalNumber(a, { unit: "hour" }) : Ue(a, t.length);
}, m: function(e, t, n) {
  return t === "mo" ? n.ordinalNumber(e.getMinutes(), { unit: "minute" }) : ea.m(e, t);
}, s: function(e, t, n) {
  return t === "so" ? n.ordinalNumber(e.getSeconds(), { unit: "second" }) : ea.s(e, t);
}, S: function(e, t) {
  return ea.S(e, t);
}, X: function(e, t, n) {
  const a = e.getTimezoneOffset();
  if (a === 0) return "Z";
  switch (t) {
    case "X":
      return Vu(a);
    case "XXXX":
    case "XX":
      return Pa(a);
    case "XXXXX":
    case "XXX":
    default:
      return Pa(a, ":");
  }
}, x: function(e, t, n) {
  const a = e.getTimezoneOffset();
  switch (t) {
    case "x":
      return Vu(a);
    case "xxxx":
    case "xx":
      return Pa(a);
    case "xxxxx":
    case "xxx":
    default:
      return Pa(a, ":");
  }
}, O: function(e, t, n) {
  const a = e.getTimezoneOffset();
  switch (t) {
    case "O":
    case "OO":
    case "OOO":
      return "GMT" + Iu(a, ":");
    case "OOOO":
    default:
      return "GMT" + Pa(a, ":");
  }
}, z: function(e, t, n) {
  const a = e.getTimezoneOffset();
  switch (t) {
    case "z":
    case "zz":
    case "zzz":
      return "GMT" + Iu(a, ":");
    case "zzzz":
    default:
      return "GMT" + Pa(a, ":");
  }
}, t: function(e, t, n) {
  const a = Math.trunc(e.getTime() / 1e3);
  return Ue(a, t.length);
}, T: function(e, t, n) {
  const a = e.getTime();
  return Ue(a, t.length);
} };
function Iu(e, t = "") {
  const n = e > 0 ? "-" : "+", a = Math.abs(e), r = Math.trunc(a / 60), l = a % 60;
  return l === 0 ? n + String(r) : n + String(r) + t + Ue(l, 2);
}
function Vu(e, t) {
  return e % 60 === 0 ? (e > 0 ? "-" : "+") + Ue(Math.abs(e) / 60, 2) : Pa(e, t);
}
function Pa(e, t = "") {
  const n = e > 0 ? "-" : "+", a = Math.abs(e), r = Ue(Math.trunc(a / 60), 2), l = Ue(a % 60, 2);
  return n + r + t + l;
}
const ju = (e, t) => {
  switch (e) {
    case "P":
      return t.date({ width: "short" });
    case "PP":
      return t.date({ width: "medium" });
    case "PPP":
      return t.date({ width: "long" });
    case "PPPP":
    default:
      return t.date({ width: "full" });
  }
}, Xd = (e, t) => {
  switch (e) {
    case "p":
      return t.time({ width: "short" });
    case "pp":
      return t.time({ width: "medium" });
    case "ppp":
      return t.time({ width: "long" });
    case "pppp":
    default:
      return t.time({ width: "full" });
  }
}, nh = (e, t) => {
  const n = e.match(/(P+)(p+)?/) || [], a = n[1], r = n[2];
  if (!r) return ju(e, t);
  let l;
  switch (a) {
    case "P":
      l = t.dateTime({ width: "short" });
      break;
    case "PP":
      l = t.dateTime({ width: "medium" });
      break;
    case "PPP":
      l = t.dateTime({ width: "long" });
      break;
    case "PPPP":
    default:
      l = t.dateTime({ width: "full" });
      break;
  }
  return l.replace("{{date}}", ju(a, t)).replace("{{time}}", Xd(r, t));
}, xi = { p: Xd, P: nh }, ah = /^D+$/, rh = /^Y+$/, lh = ["D", "DD", "YY", "YYYY"];
function Jd(e) {
  return ah.test(e);
}
function ep(e) {
  return rh.test(e);
}
function ki(e, t, n) {
  const a = oh(e, t, n);
  if (console.warn(a), lh.includes(e)) throw new RangeError(a);
}
function oh(e, t, n) {
  const a = e[0] === "Y" ? "years" : "days of the month";
  return `Use \`${e.toLowerCase()}\` instead of \`${e}\` (in \`${t}\`) for formatting ${a} to the input \`${n}\`; see: https://github.com/date-fns/date-fns/blob/master/docs/unicodeTokens.md`;
}
const ih = /[yYQqMLwIdDecihHKkms]o|(\w)\1*|''|'(''|[^'])+('|$)|./g, sh = /P+p+|P+|p+|''|'(''|[^'])+('|$)|./g, uh = /^'([^]*?)'?$/, ch = /''/g, dh = /[a-zA-Z]/;
function Ln(e, t, n) {
  var a, r, l, o, i, s, c, d;
  const u = Ka(), p = (n == null ? void 0 : n.locale) ?? u.locale ?? Qd, v = (n == null ? void 0 : n.firstWeekContainsDate) ?? ((r = (a = n == null ? void 0 : n.locale) == null ? void 0 : a.options) == null ? void 0 : r.firstWeekContainsDate) ?? u.firstWeekContainsDate ?? ((o = (l = u.locale) == null ? void 0 : l.options) == null ? void 0 : o.firstWeekContainsDate) ?? 1, b = (n == null ? void 0 : n.weekStartsOn) ?? ((s = (i = n == null ? void 0 : n.locale) == null ? void 0 : i.options) == null ? void 0 : s.weekStartsOn) ?? u.weekStartsOn ?? ((d = (c = u.locale) == null ? void 0 : c.options) == null ? void 0 : d.weekStartsOn) ?? 0, h = Te(e);
  if (!Yr(h)) throw new RangeError("Invalid time value");
  let N = t.match(sh).map((x) => {
    const _ = x[0];
    if (_ === "p" || _ === "P") {
      const g = xi[_];
      return g(x, p.formatLong);
    }
    return x;
  }).join("").match(ih).map((x) => {
    if (x === "''") return { isToken: false, value: "'" };
    const _ = x[0];
    if (_ === "'") return { isToken: false, value: ph(x) };
    if (Nu[_]) return { isToken: true, value: x };
    if (_.match(dh)) throw new RangeError("Format string contains an unescaped latin alphabet character `" + _ + "`");
    return { isToken: false, value: x };
  });
  p.localize.preprocessor && (N = p.localize.preprocessor(h, N));
  const I = { firstWeekContainsDate: v, weekStartsOn: b, locale: p };
  return N.map((x) => {
    if (!x.isToken) return x.value;
    const _ = x.value;
    (!(n != null && n.useAdditionalWeekYearTokens) && ep(_) || !(n != null && n.useAdditionalDayOfYearTokens) && Jd(_)) && ki(_, t, String(e));
    const g = Nu[_[0]];
    return g(h, _, p.localize, I);
  }).join("");
}
function ph(e) {
  const t = e.match(uh);
  return t ? t[1].replace(ch, "'") : e;
}
function fh(e) {
  return Te(e).getDay();
}
function vh(e) {
  const t = Te(e), n = t.getFullYear(), a = t.getMonth(), r = Ke(e, 0);
  return r.setFullYear(n, a + 1, 0), r.setHours(0, 0, 0, 0), r.getDate();
}
function mh() {
  return Object.assign({}, Ka());
}
function Wn(e) {
  return Te(e).getHours();
}
function hh(e) {
  let t = Te(e).getDay();
  return t === 0 && (t = 7), t;
}
function ha(e) {
  return Te(e).getMinutes();
}
function Be(e) {
  return Te(e).getMonth();
}
function cr(e) {
  return Te(e).getSeconds();
}
function Re(e) {
  return Te(e).getFullYear();
}
function dr(e, t) {
  const n = Te(e), a = Te(t);
  return n.getTime() > a.getTime();
}
function el(e, t) {
  const n = Te(e), a = Te(t);
  return +n < +a;
}
function er(e, t) {
  const n = Te(e), a = Te(t);
  return +n == +a;
}
function gh(e, t) {
  const n = t instanceof Date ? Ke(t, 0) : new t(0);
  return n.setFullYear(e.getFullYear(), e.getMonth(), e.getDate()), n.setHours(e.getHours(), e.getMinutes(), e.getSeconds(), e.getMilliseconds()), n;
}
const wh = 10;
class tp {
  constructor() {
    Me(this, "subPriority", 0);
  }
  validate(t, n) {
    return true;
  }
}
class yh extends tp {
  constructor(t, n, a, r, l) {
    super(), this.value = t, this.validateValue = n, this.setValue = a, this.priority = r, l && (this.subPriority = l);
  }
  validate(t, n) {
    return this.validateValue(t, this.value, n);
  }
  set(t, n, a) {
    return this.setValue(t, n, this.value, a);
  }
}
class bh extends tp {
  constructor() {
    super(...arguments), Me(this, "priority", wh), Me(this, "subPriority", -1);
  }
  set(t, n) {
    return n.timestampIsSet ? t : Ke(t, gh(t, Date));
  }
}
class Ze {
  run(t, n, a, r) {
    const l = this.parse(t, n, a, r);
    return l ? { setter: new yh(l.value, this.validate, this.set, this.priority, this.subPriority), rest: l.rest } : null;
  }
  validate(t, n, a) {
    return true;
  }
}
class _h extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 140), Me(this, "incompatibleTokens", ["R", "u", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "G":
      case "GG":
      case "GGG":
        return a.era(t, { width: "abbreviated" }) || a.era(t, { width: "narrow" });
      case "GGGGG":
        return a.era(t, { width: "narrow" });
      case "GGGG":
      default:
        return a.era(t, { width: "wide" }) || a.era(t, { width: "abbreviated" }) || a.era(t, { width: "narrow" });
    }
  }
  set(t, n, a) {
    return n.era = a, t.setFullYear(a, 0, 1), t.setHours(0, 0, 0, 0), t;
  }
}
const wt = { month: /^(1[0-2]|0?\d)/, date: /^(3[0-1]|[0-2]?\d)/, dayOfYear: /^(36[0-6]|3[0-5]\d|[0-2]?\d?\d)/, week: /^(5[0-3]|[0-4]?\d)/, hour23h: /^(2[0-3]|[0-1]?\d)/, hour24h: /^(2[0-4]|[0-1]?\d)/, hour11h: /^(1[0-1]|0?\d)/, hour12h: /^(1[0-2]|0?\d)/, minute: /^[0-5]?\d/, second: /^[0-5]?\d/, singleDigit: /^\d/, twoDigits: /^\d{1,2}/, threeDigits: /^\d{1,3}/, fourDigits: /^\d{1,4}/, anyDigitsSigned: /^-?\d+/, singleDigitSigned: /^-?\d/, twoDigitsSigned: /^-?\d{1,2}/, threeDigitsSigned: /^-?\d{1,3}/, fourDigitsSigned: /^-?\d{1,4}/ }, Mn = { basicOptionalMinutes: /^([+-])(\d{2})(\d{2})?|Z/, basic: /^([+-])(\d{2})(\d{2})|Z/, basicOptionalSeconds: /^([+-])(\d{2})(\d{2})((\d{2}))?|Z/, extended: /^([+-])(\d{2}):(\d{2})|Z/, extendedOptionalSeconds: /^([+-])(\d{2}):(\d{2})(:(\d{2}))?|Z/ };
function yt(e, t) {
  return e && { value: t(e.value), rest: e.rest };
}
function ut(e, t) {
  const n = t.match(e);
  return n ? { value: parseInt(n[0], 10), rest: t.slice(n[0].length) } : null;
}
function Tn(e, t) {
  const n = t.match(e);
  if (!n) return null;
  if (n[0] === "Z") return { value: 0, rest: t.slice(1) };
  const a = n[1] === "+" ? 1 : -1, r = n[2] ? parseInt(n[2], 10) : 0, l = n[3] ? parseInt(n[3], 10) : 0, o = n[5] ? parseInt(n[5], 10) : 0;
  return { value: a * (r * zd + l * pm + o * fm), rest: t.slice(n[0].length) };
}
function np(e) {
  return ut(wt.anyDigitsSigned, e);
}
function vt(e, t) {
  switch (e) {
    case 1:
      return ut(wt.singleDigit, t);
    case 2:
      return ut(wt.twoDigits, t);
    case 3:
      return ut(wt.threeDigits, t);
    case 4:
      return ut(wt.fourDigits, t);
    default:
      return ut(new RegExp("^\\d{1," + e + "}"), t);
  }
}
function ap(e, t) {
  switch (e) {
    case 1:
      return ut(wt.singleDigitSigned, t);
    case 2:
      return ut(wt.twoDigitsSigned, t);
    case 3:
      return ut(wt.threeDigitsSigned, t);
    case 4:
      return ut(wt.fourDigitsSigned, t);
    default:
      return ut(new RegExp("^-?\\d{1," + e + "}"), t);
  }
}
function rs(e) {
  switch (e) {
    case "morning":
      return 4;
    case "evening":
      return 17;
    case "pm":
    case "noon":
    case "afternoon":
      return 12;
    case "am":
    case "midnight":
    case "night":
    default:
      return 0;
  }
}
function rp(e, t) {
  const n = t > 0, a = n ? t : 1 - t;
  let r;
  if (a <= 50) r = e || 100;
  else {
    const l = a + 50, o = Math.trunc(l / 100) * 100, i = e >= l % 100;
    r = e + o - (i ? 100 : 0);
  }
  return n ? r : 1 - r;
}
function lp(e) {
  return e % 400 === 0 || e % 4 === 0 && e % 100 !== 0;
}
class xh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 130), Me(this, "incompatibleTokens", ["Y", "R", "u", "w", "I", "i", "e", "c", "t", "T"]);
  }
  parse(t, n, a) {
    const r = (l) => ({ year: l, isTwoDigitYear: n === "yy" });
    switch (n) {
      case "y":
        return yt(vt(4, t), r);
      case "yo":
        return yt(a.ordinalNumber(t, { unit: "year" }), r);
      default:
        return yt(vt(n.length, t), r);
    }
  }
  validate(t, n) {
    return n.isTwoDigitYear || n.year > 0;
  }
  set(t, n, a) {
    const r = t.getFullYear();
    if (a.isTwoDigitYear) {
      const o = rp(a.year, r);
      return t.setFullYear(o, 0, 1), t.setHours(0, 0, 0, 0), t;
    }
    const l = !("era" in n) || n.era === 1 ? a.year : 1 - a.year;
    return t.setFullYear(l, 0, 1), t.setHours(0, 0, 0, 0), t;
  }
}
class kh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 130), Me(this, "incompatibleTokens", ["y", "R", "u", "Q", "q", "M", "L", "I", "d", "D", "i", "t", "T"]);
  }
  parse(t, n, a) {
    const r = (l) => ({ year: l, isTwoDigitYear: n === "YY" });
    switch (n) {
      case "Y":
        return yt(vt(4, t), r);
      case "Yo":
        return yt(a.ordinalNumber(t, { unit: "year" }), r);
      default:
        return yt(vt(n.length, t), r);
    }
  }
  validate(t, n) {
    return n.isTwoDigitYear || n.year > 0;
  }
  set(t, n, a, r) {
    const l = ns(t, r);
    if (a.isTwoDigitYear) {
      const i = rp(a.year, l);
      return t.setFullYear(i, 0, r.firstWeekContainsDate), t.setHours(0, 0, 0, 0), bn(t, r);
    }
    const o = !("era" in n) || n.era === 1 ? a.year : 1 - a.year;
    return t.setFullYear(o, 0, r.firstWeekContainsDate), t.setHours(0, 0, 0, 0), bn(t, r);
  }
}
class Sh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 130), Me(this, "incompatibleTokens", ["G", "y", "Y", "u", "Q", "q", "M", "L", "w", "d", "D", "e", "c", "t", "T"]);
  }
  parse(t, n) {
    return ap(n === "R" ? 4 : n.length, t);
  }
  set(t, n, a) {
    const r = Ke(t, 0);
    return r.setFullYear(a, 0, 4), r.setHours(0, 0, 0, 0), ur(r);
  }
}
class Ch extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 130), Me(this, "incompatibleTokens", ["G", "y", "Y", "R", "w", "I", "i", "e", "c", "t", "T"]);
  }
  parse(t, n) {
    return ap(n === "u" ? 4 : n.length, t);
  }
  set(t, n, a) {
    return t.setFullYear(a, 0, 1), t.setHours(0, 0, 0, 0), t;
  }
}
class Mh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 120), Me(this, "incompatibleTokens", ["Y", "R", "q", "M", "L", "w", "I", "d", "D", "i", "e", "c", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "Q":
      case "QQ":
        return vt(n.length, t);
      case "Qo":
        return a.ordinalNumber(t, { unit: "quarter" });
      case "QQQ":
        return a.quarter(t, { width: "abbreviated", context: "formatting" }) || a.quarter(t, { width: "narrow", context: "formatting" });
      case "QQQQQ":
        return a.quarter(t, { width: "narrow", context: "formatting" });
      case "QQQQ":
      default:
        return a.quarter(t, { width: "wide", context: "formatting" }) || a.quarter(t, { width: "abbreviated", context: "formatting" }) || a.quarter(t, { width: "narrow", context: "formatting" });
    }
  }
  validate(t, n) {
    return n >= 1 && n <= 4;
  }
  set(t, n, a) {
    return t.setMonth((a - 1) * 3, 1), t.setHours(0, 0, 0, 0), t;
  }
}
class Th extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 120), Me(this, "incompatibleTokens", ["Y", "R", "Q", "M", "L", "w", "I", "d", "D", "i", "e", "c", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "q":
      case "qq":
        return vt(n.length, t);
      case "qo":
        return a.ordinalNumber(t, { unit: "quarter" });
      case "qqq":
        return a.quarter(t, { width: "abbreviated", context: "standalone" }) || a.quarter(t, { width: "narrow", context: "standalone" });
      case "qqqqq":
        return a.quarter(t, { width: "narrow", context: "standalone" });
      case "qqqq":
      default:
        return a.quarter(t, { width: "wide", context: "standalone" }) || a.quarter(t, { width: "abbreviated", context: "standalone" }) || a.quarter(t, { width: "narrow", context: "standalone" });
    }
  }
  validate(t, n) {
    return n >= 1 && n <= 4;
  }
  set(t, n, a) {
    return t.setMonth((a - 1) * 3, 1), t.setHours(0, 0, 0, 0), t;
  }
}
class Ah extends Ze {
  constructor() {
    super(...arguments), Me(this, "incompatibleTokens", ["Y", "R", "q", "Q", "L", "w", "I", "D", "i", "e", "c", "t", "T"]), Me(this, "priority", 110);
  }
  parse(t, n, a) {
    const r = (l) => l - 1;
    switch (n) {
      case "M":
        return yt(ut(wt.month, t), r);
      case "MM":
        return yt(vt(2, t), r);
      case "Mo":
        return yt(a.ordinalNumber(t, { unit: "month" }), r);
      case "MMM":
        return a.month(t, { width: "abbreviated", context: "formatting" }) || a.month(t, { width: "narrow", context: "formatting" });
      case "MMMMM":
        return a.month(t, { width: "narrow", context: "formatting" });
      case "MMMM":
      default:
        return a.month(t, { width: "wide", context: "formatting" }) || a.month(t, { width: "abbreviated", context: "formatting" }) || a.month(t, { width: "narrow", context: "formatting" });
    }
  }
  validate(t, n) {
    return n >= 0 && n <= 11;
  }
  set(t, n, a) {
    return t.setMonth(a, 1), t.setHours(0, 0, 0, 0), t;
  }
}
class Dh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 110), Me(this, "incompatibleTokens", ["Y", "R", "q", "Q", "M", "w", "I", "D", "i", "e", "c", "t", "T"]);
  }
  parse(t, n, a) {
    const r = (l) => l - 1;
    switch (n) {
      case "L":
        return yt(ut(wt.month, t), r);
      case "LL":
        return yt(vt(2, t), r);
      case "Lo":
        return yt(a.ordinalNumber(t, { unit: "month" }), r);
      case "LLL":
        return a.month(t, { width: "abbreviated", context: "standalone" }) || a.month(t, { width: "narrow", context: "standalone" });
      case "LLLLL":
        return a.month(t, { width: "narrow", context: "standalone" });
      case "LLLL":
      default:
        return a.month(t, { width: "wide", context: "standalone" }) || a.month(t, { width: "abbreviated", context: "standalone" }) || a.month(t, { width: "narrow", context: "standalone" });
    }
  }
  validate(t, n) {
    return n >= 0 && n <= 11;
  }
  set(t, n, a) {
    return t.setMonth(a, 1), t.setHours(0, 0, 0, 0), t;
  }
}
function Lh(e, t, n) {
  const a = Te(e), r = as(a, n) - t;
  return a.setDate(a.getDate() - r * 7), a;
}
class Oh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 100), Me(this, "incompatibleTokens", ["y", "R", "u", "q", "Q", "M", "L", "I", "d", "D", "i", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "w":
        return ut(wt.week, t);
      case "wo":
        return a.ordinalNumber(t, { unit: "week" });
      default:
        return vt(n.length, t);
    }
  }
  validate(t, n) {
    return n >= 1 && n <= 53;
  }
  set(t, n, a, r) {
    return bn(Lh(t, a, r), r);
  }
}
function Ph(e, t) {
  const n = Te(e), a = ts(n) - t;
  return n.setDate(n.getDate() - a * 7), n;
}
class $h extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 100), Me(this, "incompatibleTokens", ["y", "Y", "u", "q", "Q", "M", "L", "w", "d", "D", "e", "c", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "I":
        return ut(wt.week, t);
      case "Io":
        return a.ordinalNumber(t, { unit: "week" });
      default:
        return vt(n.length, t);
    }
  }
  validate(t, n) {
    return n >= 1 && n <= 53;
  }
  set(t, n, a) {
    return ur(Ph(t, a));
  }
}
const Rh = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31], Eh = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
class Nh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 90), Me(this, "subPriority", 1), Me(this, "incompatibleTokens", ["Y", "R", "q", "Q", "w", "I", "D", "i", "e", "c", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "d":
        return ut(wt.date, t);
      case "do":
        return a.ordinalNumber(t, { unit: "date" });
      default:
        return vt(n.length, t);
    }
  }
  validate(t, n) {
    const a = t.getFullYear(), r = lp(a), l = t.getMonth();
    return r ? n >= 1 && n <= Eh[l] : n >= 1 && n <= Rh[l];
  }
  set(t, n, a) {
    return t.setDate(a), t.setHours(0, 0, 0, 0), t;
  }
}
class Ih extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 90), Me(this, "subpriority", 1), Me(this, "incompatibleTokens", ["Y", "R", "q", "Q", "M", "L", "w", "I", "d", "E", "i", "e", "c", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "D":
      case "DD":
        return ut(wt.dayOfYear, t);
      case "Do":
        return a.ordinalNumber(t, { unit: "date" });
      default:
        return vt(n.length, t);
    }
  }
  validate(t, n) {
    const a = t.getFullYear();
    return lp(a) ? n >= 1 && n <= 366 : n >= 1 && n <= 365;
  }
  set(t, n, a) {
    return t.setMonth(0, a), t.setHours(0, 0, 0, 0), t;
  }
}
function ls(e, t, n) {
  var a, r, l, o;
  const i = Ka(), s = (n == null ? void 0 : n.weekStartsOn) ?? ((r = (a = n == null ? void 0 : n.locale) == null ? void 0 : a.options) == null ? void 0 : r.weekStartsOn) ?? i.weekStartsOn ?? ((o = (l = i.locale) == null ? void 0 : l.options) == null ? void 0 : o.weekStartsOn) ?? 0, c = Te(e), d = c.getDay(), u = (t % 7 + 7) % 7, p = 7 - s, v = t < 0 || t > 6 ? t - (d + p) % 7 : (u + p) % 7 - (d + p) % 7;
  return pn(c, v);
}
class Vh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 90), Me(this, "incompatibleTokens", ["D", "i", "e", "c", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "E":
      case "EE":
      case "EEE":
        return a.day(t, { width: "abbreviated", context: "formatting" }) || a.day(t, { width: "short", context: "formatting" }) || a.day(t, { width: "narrow", context: "formatting" });
      case "EEEEE":
        return a.day(t, { width: "narrow", context: "formatting" });
      case "EEEEEE":
        return a.day(t, { width: "short", context: "formatting" }) || a.day(t, { width: "narrow", context: "formatting" });
      case "EEEE":
      default:
        return a.day(t, { width: "wide", context: "formatting" }) || a.day(t, { width: "abbreviated", context: "formatting" }) || a.day(t, { width: "short", context: "formatting" }) || a.day(t, { width: "narrow", context: "formatting" });
    }
  }
  validate(t, n) {
    return n >= 0 && n <= 6;
  }
  set(t, n, a, r) {
    return t = ls(t, a, r), t.setHours(0, 0, 0, 0), t;
  }
}
class jh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 90), Me(this, "incompatibleTokens", ["y", "R", "u", "q", "Q", "M", "L", "I", "d", "D", "E", "i", "c", "t", "T"]);
  }
  parse(t, n, a, r) {
    const l = (o) => {
      const i = Math.floor((o - 1) / 7) * 7;
      return (o + r.weekStartsOn + 6) % 7 + i;
    };
    switch (n) {
      case "e":
      case "ee":
        return yt(vt(n.length, t), l);
      case "eo":
        return yt(a.ordinalNumber(t, { unit: "day" }), l);
      case "eee":
        return a.day(t, { width: "abbreviated", context: "formatting" }) || a.day(t, { width: "short", context: "formatting" }) || a.day(t, { width: "narrow", context: "formatting" });
      case "eeeee":
        return a.day(t, { width: "narrow", context: "formatting" });
      case "eeeeee":
        return a.day(t, { width: "short", context: "formatting" }) || a.day(t, { width: "narrow", context: "formatting" });
      case "eeee":
      default:
        return a.day(t, { width: "wide", context: "formatting" }) || a.day(t, { width: "abbreviated", context: "formatting" }) || a.day(t, { width: "short", context: "formatting" }) || a.day(t, { width: "narrow", context: "formatting" });
    }
  }
  validate(t, n) {
    return n >= 0 && n <= 6;
  }
  set(t, n, a, r) {
    return t = ls(t, a, r), t.setHours(0, 0, 0, 0), t;
  }
}
class Bh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 90), Me(this, "incompatibleTokens", ["y", "R", "u", "q", "Q", "M", "L", "I", "d", "D", "E", "i", "e", "t", "T"]);
  }
  parse(t, n, a, r) {
    const l = (o) => {
      const i = Math.floor((o - 1) / 7) * 7;
      return (o + r.weekStartsOn + 6) % 7 + i;
    };
    switch (n) {
      case "c":
      case "cc":
        return yt(vt(n.length, t), l);
      case "co":
        return yt(a.ordinalNumber(t, { unit: "day" }), l);
      case "ccc":
        return a.day(t, { width: "abbreviated", context: "standalone" }) || a.day(t, { width: "short", context: "standalone" }) || a.day(t, { width: "narrow", context: "standalone" });
      case "ccccc":
        return a.day(t, { width: "narrow", context: "standalone" });
      case "cccccc":
        return a.day(t, { width: "short", context: "standalone" }) || a.day(t, { width: "narrow", context: "standalone" });
      case "cccc":
      default:
        return a.day(t, { width: "wide", context: "standalone" }) || a.day(t, { width: "abbreviated", context: "standalone" }) || a.day(t, { width: "short", context: "standalone" }) || a.day(t, { width: "narrow", context: "standalone" });
    }
  }
  validate(t, n) {
    return n >= 0 && n <= 6;
  }
  set(t, n, a, r) {
    return t = ls(t, a, r), t.setHours(0, 0, 0, 0), t;
  }
}
function Fh(e, t) {
  const n = Te(e), a = hh(n), r = t - a;
  return pn(n, r);
}
class Yh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 90), Me(this, "incompatibleTokens", ["y", "Y", "u", "q", "Q", "M", "L", "w", "d", "D", "E", "e", "c", "t", "T"]);
  }
  parse(t, n, a) {
    const r = (l) => l === 0 ? 7 : l;
    switch (n) {
      case "i":
      case "ii":
        return vt(n.length, t);
      case "io":
        return a.ordinalNumber(t, { unit: "day" });
      case "iii":
        return yt(a.day(t, { width: "abbreviated", context: "formatting" }) || a.day(t, { width: "short", context: "formatting" }) || a.day(t, { width: "narrow", context: "formatting" }), r);
      case "iiiii":
        return yt(a.day(t, { width: "narrow", context: "formatting" }), r);
      case "iiiiii":
        return yt(a.day(t, { width: "short", context: "formatting" }) || a.day(t, { width: "narrow", context: "formatting" }), r);
      case "iiii":
      default:
        return yt(a.day(t, { width: "wide", context: "formatting" }) || a.day(t, { width: "abbreviated", context: "formatting" }) || a.day(t, { width: "short", context: "formatting" }) || a.day(t, { width: "narrow", context: "formatting" }), r);
    }
  }
  validate(t, n) {
    return n >= 1 && n <= 7;
  }
  set(t, n, a) {
    return t = Fh(t, a), t.setHours(0, 0, 0, 0), t;
  }
}
class qh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 80), Me(this, "incompatibleTokens", ["b", "B", "H", "k", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "a":
      case "aa":
      case "aaa":
        return a.dayPeriod(t, { width: "abbreviated", context: "formatting" }) || a.dayPeriod(t, { width: "narrow", context: "formatting" });
      case "aaaaa":
        return a.dayPeriod(t, { width: "narrow", context: "formatting" });
      case "aaaa":
      default:
        return a.dayPeriod(t, { width: "wide", context: "formatting" }) || a.dayPeriod(t, { width: "abbreviated", context: "formatting" }) || a.dayPeriod(t, { width: "narrow", context: "formatting" });
    }
  }
  set(t, n, a) {
    return t.setHours(rs(a), 0, 0, 0), t;
  }
}
class zh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 80), Me(this, "incompatibleTokens", ["a", "B", "H", "k", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "b":
      case "bb":
      case "bbb":
        return a.dayPeriod(t, { width: "abbreviated", context: "formatting" }) || a.dayPeriod(t, { width: "narrow", context: "formatting" });
      case "bbbbb":
        return a.dayPeriod(t, { width: "narrow", context: "formatting" });
      case "bbbb":
      default:
        return a.dayPeriod(t, { width: "wide", context: "formatting" }) || a.dayPeriod(t, { width: "abbreviated", context: "formatting" }) || a.dayPeriod(t, { width: "narrow", context: "formatting" });
    }
  }
  set(t, n, a) {
    return t.setHours(rs(a), 0, 0, 0), t;
  }
}
class Hh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 80), Me(this, "incompatibleTokens", ["a", "b", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "B":
      case "BB":
      case "BBB":
        return a.dayPeriod(t, { width: "abbreviated", context: "formatting" }) || a.dayPeriod(t, { width: "narrow", context: "formatting" });
      case "BBBBB":
        return a.dayPeriod(t, { width: "narrow", context: "formatting" });
      case "BBBB":
      default:
        return a.dayPeriod(t, { width: "wide", context: "formatting" }) || a.dayPeriod(t, { width: "abbreviated", context: "formatting" }) || a.dayPeriod(t, { width: "narrow", context: "formatting" });
    }
  }
  set(t, n, a) {
    return t.setHours(rs(a), 0, 0, 0), t;
  }
}
class Kh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 70), Me(this, "incompatibleTokens", ["H", "K", "k", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "h":
        return ut(wt.hour12h, t);
      case "ho":
        return a.ordinalNumber(t, { unit: "hour" });
      default:
        return vt(n.length, t);
    }
  }
  validate(t, n) {
    return n >= 1 && n <= 12;
  }
  set(t, n, a) {
    const r = t.getHours() >= 12;
    return r && a < 12 ? t.setHours(a + 12, 0, 0, 0) : !r && a === 12 ? t.setHours(0, 0, 0, 0) : t.setHours(a, 0, 0, 0), t;
  }
}
class Zh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 70), Me(this, "incompatibleTokens", ["a", "b", "h", "K", "k", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "H":
        return ut(wt.hour23h, t);
      case "Ho":
        return a.ordinalNumber(t, { unit: "hour" });
      default:
        return vt(n.length, t);
    }
  }
  validate(t, n) {
    return n >= 0 && n <= 23;
  }
  set(t, n, a) {
    return t.setHours(a, 0, 0, 0), t;
  }
}
class Wh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 70), Me(this, "incompatibleTokens", ["h", "H", "k", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "K":
        return ut(wt.hour11h, t);
      case "Ko":
        return a.ordinalNumber(t, { unit: "hour" });
      default:
        return vt(n.length, t);
    }
  }
  validate(t, n) {
    return n >= 0 && n <= 11;
  }
  set(t, n, a) {
    return t.getHours() >= 12 && a < 12 ? t.setHours(a + 12, 0, 0, 0) : t.setHours(a, 0, 0, 0), t;
  }
}
class Uh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 70), Me(this, "incompatibleTokens", ["a", "b", "h", "H", "K", "t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "k":
        return ut(wt.hour24h, t);
      case "ko":
        return a.ordinalNumber(t, { unit: "hour" });
      default:
        return vt(n.length, t);
    }
  }
  validate(t, n) {
    return n >= 1 && n <= 24;
  }
  set(t, n, a) {
    const r = a <= 24 ? a % 24 : a;
    return t.setHours(r, 0, 0, 0), t;
  }
}
class Gh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 60), Me(this, "incompatibleTokens", ["t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "m":
        return ut(wt.minute, t);
      case "mo":
        return a.ordinalNumber(t, { unit: "minute" });
      default:
        return vt(n.length, t);
    }
  }
  validate(t, n) {
    return n >= 0 && n <= 59;
  }
  set(t, n, a) {
    return t.setMinutes(a, 0, 0), t;
  }
}
class Qh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 50), Me(this, "incompatibleTokens", ["t", "T"]);
  }
  parse(t, n, a) {
    switch (n) {
      case "s":
        return ut(wt.second, t);
      case "so":
        return a.ordinalNumber(t, { unit: "second" });
      default:
        return vt(n.length, t);
    }
  }
  validate(t, n) {
    return n >= 0 && n <= 59;
  }
  set(t, n, a) {
    return t.setSeconds(a, 0), t;
  }
}
class Xh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 30), Me(this, "incompatibleTokens", ["t", "T"]);
  }
  parse(t, n) {
    const a = (r) => Math.trunc(r * Math.pow(10, -n.length + 3));
    return yt(vt(n.length, t), a);
  }
  set(t, n, a) {
    return t.setMilliseconds(a), t;
  }
}
class Jh extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 10), Me(this, "incompatibleTokens", ["t", "T", "x"]);
  }
  parse(t, n) {
    switch (n) {
      case "X":
        return Tn(Mn.basicOptionalMinutes, t);
      case "XX":
        return Tn(Mn.basic, t);
      case "XXXX":
        return Tn(Mn.basicOptionalSeconds, t);
      case "XXXXX":
        return Tn(Mn.extendedOptionalSeconds, t);
      case "XXX":
      default:
        return Tn(Mn.extended, t);
    }
  }
  set(t, n, a) {
    return n.timestampIsSet ? t : Ke(t, t.getTime() - zl(t) - a);
  }
}
class eg extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 10), Me(this, "incompatibleTokens", ["t", "T", "X"]);
  }
  parse(t, n) {
    switch (n) {
      case "x":
        return Tn(Mn.basicOptionalMinutes, t);
      case "xx":
        return Tn(Mn.basic, t);
      case "xxxx":
        return Tn(Mn.basicOptionalSeconds, t);
      case "xxxxx":
        return Tn(Mn.extendedOptionalSeconds, t);
      case "xxx":
      default:
        return Tn(Mn.extended, t);
    }
  }
  set(t, n, a) {
    return n.timestampIsSet ? t : Ke(t, t.getTime() - zl(t) - a);
  }
}
class tg extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 40), Me(this, "incompatibleTokens", "*");
  }
  parse(t) {
    return np(t);
  }
  set(t, n, a) {
    return [Ke(t, a * 1e3), { timestampIsSet: true }];
  }
}
class ng extends Ze {
  constructor() {
    super(...arguments), Me(this, "priority", 20), Me(this, "incompatibleTokens", "*");
  }
  parse(t) {
    return np(t);
  }
  set(t, n, a) {
    return [Ke(t, a), { timestampIsSet: true }];
  }
}
const ag = { G: new _h(), y: new xh(), Y: new kh(), R: new Sh(), u: new Ch(), Q: new Mh(), q: new Th(), M: new Ah(), L: new Dh(), w: new Oh(), I: new $h(), d: new Nh(), D: new Ih(), E: new Vh(), e: new jh(), c: new Bh(), i: new Yh(), a: new qh(), b: new zh(), B: new Hh(), h: new Kh(), H: new Zh(), K: new Wh(), k: new Uh(), m: new Gh(), s: new Qh(), S: new Xh(), X: new Jh(), x: new eg(), t: new tg(), T: new ng() }, rg = /[yYQqMLwIdDecihHKkms]o|(\w)\1*|''|'(''|[^'])+('|$)|./g, lg = /P+p+|P+|p+|''|'(''|[^'])+('|$)|./g, og = /^'([^]*?)'?$/, ig = /''/g, sg = /\S/, ug = /[a-zA-Z]/;
function Si(e, t, n, a) {
  var r, l, o, i, s, c, d, u;
  const p = mh(), v = (a == null ? void 0 : a.locale) ?? p.locale ?? Qd, b = (a == null ? void 0 : a.firstWeekContainsDate) ?? ((l = (r = a == null ? void 0 : a.locale) == null ? void 0 : r.options) == null ? void 0 : l.firstWeekContainsDate) ?? p.firstWeekContainsDate ?? ((i = (o = p.locale) == null ? void 0 : o.options) == null ? void 0 : i.firstWeekContainsDate) ?? 1, h = (a == null ? void 0 : a.weekStartsOn) ?? ((c = (s = a == null ? void 0 : a.locale) == null ? void 0 : s.options) == null ? void 0 : c.weekStartsOn) ?? p.weekStartsOn ?? ((u = (d = p.locale) == null ? void 0 : d.options) == null ? void 0 : u.weekStartsOn) ?? 0;
  if (t === "") return e === "" ? Te(n) : Ke(n, NaN);
  const N = { firstWeekContainsDate: b, weekStartsOn: h, locale: v }, I = [new bh()], x = t.match(lg).map((C) => {
    const Y = C[0];
    if (Y in xi) {
      const P = xi[Y];
      return P(C, v.formatLong);
    }
    return C;
  }).join("").match(rg), _ = [];
  for (let C of x) {
    !(a != null && a.useAdditionalWeekYearTokens) && ep(C) && ki(C, t, e), !(a != null && a.useAdditionalDayOfYearTokens) && Jd(C) && ki(C, t, e);
    const Y = C[0], P = ag[Y];
    if (P) {
      const { incompatibleTokens: $ } = P;
      if (Array.isArray($)) {
        const z = _.find((se) => $.includes(se.token) || se.token === Y);
        if (z) throw new RangeError(`The format string mustn't contain \`${z.fullToken}\` and \`${C}\` at the same time`);
      } else if (P.incompatibleTokens === "*" && _.length > 0) throw new RangeError(`The format string mustn't contain \`${C}\` and any other token at the same time`);
      _.push({ token: Y, fullToken: C });
      const H = P.run(e, C, v.match, N);
      if (!H) return Ke(n, NaN);
      I.push(H.setter), e = H.rest;
    } else {
      if (Y.match(ug)) throw new RangeError("Format string contains an unescaped latin alphabet character `" + Y + "`");
      if (C === "''" ? C = "'" : Y === "'" && (C = cg(C)), e.indexOf(C) === 0) e = e.slice(C.length);
      else return Ke(n, NaN);
    }
  }
  if (e.length > 0 && sg.test(e)) return Ke(n, NaN);
  const g = I.map((C) => C.priority).sort((C, Y) => Y - C).filter((C, Y, P) => P.indexOf(C) === Y).map((C) => I.filter((Y) => Y.priority === C).sort((Y, P) => P.subPriority - Y.subPriority)).map((C) => C[0]);
  let R = Te(n);
  if (isNaN(R.getTime())) return Ke(n, NaN);
  const M = {};
  for (const C of g) {
    if (!C.validate(R, N)) return Ke(n, NaN);
    const Y = C.set(R, M, N);
    Array.isArray(Y) ? (R = Y[0], Object.assign(M, Y[1])) : R = Y;
  }
  return Ke(n, R);
}
function cg(e) {
  return e.match(og)[1].replace(ig, "'");
}
function Bu(e, t) {
  const n = rr(e), a = rr(t);
  return +n == +a;
}
function dg(e, t) {
  return pn(e, -t);
}
function op(e, t) {
  const n = Te(e), a = n.getFullYear(), r = n.getDate(), l = Ke(e, 0);
  l.setFullYear(a, t, 15), l.setHours(0, 0, 0, 0);
  const o = vh(l);
  return n.setMonth(t, Math.min(r, o)), n;
}
function Je(e, t) {
  let n = Te(e);
  return isNaN(+n) ? Ke(e, NaN) : (t.year != null && n.setFullYear(t.year), t.month != null && (n = op(n, t.month)), t.date != null && n.setDate(t.date), t.hours != null && n.setHours(t.hours), t.minutes != null && n.setMinutes(t.minutes), t.seconds != null && n.setSeconds(t.seconds), t.milliseconds != null && n.setMilliseconds(t.milliseconds), n);
}
function pg(e, t) {
  const n = Te(e);
  return n.setHours(t), n;
}
function ip(e, t) {
  const n = Te(e);
  return n.setMilliseconds(t), n;
}
function fg(e, t) {
  const n = Te(e);
  return n.setMinutes(t), n;
}
function sp(e, t) {
  const n = Te(e);
  return n.setSeconds(t), n;
}
function An(e, t) {
  const n = Te(e);
  return isNaN(+n) ? Ke(e, NaN) : (n.setFullYear(t), n);
}
function pr(e, t) {
  return hn(e, -t);
}
function vg(e, t) {
  const { years: n = 0, months: a = 0, weeks: r = 0, days: l = 0, hours: o = 0, minutes: i = 0, seconds: s = 0 } = t, c = pr(e, a + n * 12), d = dg(c, l + r * 7), u = i + o * 60, p = (s + u * 60) * 1e3;
  return Ke(e, d.getTime() - p);
}
function up(e, t) {
  return es(e, -t);
}
function xr() {
  return T(), F("svg", { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 32 32", fill: "currentColor", "aria-hidden": "true", class: "dp__icon" }, [L("path", { d: "M29.333 8c0-2.208-1.792-4-4-4h-18.667c-2.208 0-4 1.792-4 4v18.667c0 2.208 1.792 4 4 4h18.667c2.208 0 4-1.792 4-4v-18.667zM26.667 8v18.667c0 0.736-0.597 1.333-1.333 1.333 0 0-18.667 0-18.667 0-0.736 0-1.333-0.597-1.333-1.333 0 0 0-18.667 0-18.667 0-0.736 0.597-1.333 1.333-1.333 0 0 18.667 0 18.667 0 0.736 0 1.333 0.597 1.333 1.333z" }), L("path", { d: "M20 2.667v5.333c0 0.736 0.597 1.333 1.333 1.333s1.333-0.597 1.333-1.333v-5.333c0-0.736-0.597-1.333-1.333-1.333s-1.333 0.597-1.333 1.333z" }), L("path", { d: "M9.333 2.667v5.333c0 0.736 0.597 1.333 1.333 1.333s1.333-0.597 1.333-1.333v-5.333c0-0.736-0.597-1.333-1.333-1.333s-1.333 0.597-1.333 1.333z" }), L("path", { d: "M4 14.667h24c0.736 0 1.333-0.597 1.333-1.333s-0.597-1.333-1.333-1.333h-24c-0.736 0-1.333 0.597-1.333 1.333s0.597 1.333 1.333 1.333z" })]);
}
xr.compatConfig = { MODE: 3 };
function cp() {
  return T(), F("svg", { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 32 32", fill: "currentColor", "aria-hidden": "true", class: "dp__icon" }, [L("path", { d: "M23.057 7.057l-16 16c-0.52 0.52-0.52 1.365 0 1.885s1.365 0.52 1.885 0l16-16c0.52-0.52 0.52-1.365 0-1.885s-1.365-0.52-1.885 0z" }), L("path", { d: "M7.057 8.943l16 16c0.52 0.52 1.365 0.52 1.885 0s0.52-1.365 0-1.885l-16-16c-0.52-0.52-1.365-0.52-1.885 0s-0.52 1.365 0 1.885z" })]);
}
cp.compatConfig = { MODE: 3 };
function os() {
  return T(), F("svg", { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 32 32", fill: "currentColor", "aria-hidden": "true", class: "dp__icon" }, [L("path", { d: "M20.943 23.057l-7.057-7.057c0 0 7.057-7.057 7.057-7.057 0.52-0.52 0.52-1.365 0-1.885s-1.365-0.52-1.885 0l-8 8c-0.521 0.521-0.521 1.365 0 1.885l8 8c0.52 0.52 1.365 0.52 1.885 0s0.52-1.365 0-1.885z" })]);
}
os.compatConfig = { MODE: 3 };
function is() {
  return T(), F("svg", { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 32 32", fill: "currentColor", "aria-hidden": "true", class: "dp__icon" }, [L("path", { d: "M12.943 24.943l8-8c0.521-0.521 0.521-1.365 0-1.885l-8-8c-0.52-0.52-1.365-0.52-1.885 0s-0.52 1.365 0 1.885l7.057 7.057c0 0-7.057 7.057-7.057 7.057-0.52 0.52-0.52 1.365 0 1.885s1.365 0.52 1.885 0z" })]);
}
is.compatConfig = { MODE: 3 };
function ss() {
  return T(), F("svg", { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 32 32", fill: "currentColor", "aria-hidden": "true", class: "dp__icon" }, [L("path", { d: "M16 1.333c-8.095 0-14.667 6.572-14.667 14.667s6.572 14.667 14.667 14.667c8.095 0 14.667-6.572 14.667-14.667s-6.572-14.667-14.667-14.667zM16 4c6.623 0 12 5.377 12 12s-5.377 12-12 12c-6.623 0-12-5.377-12-12s5.377-12 12-12z" }), L("path", { d: "M14.667 8v8c0 0.505 0.285 0.967 0.737 1.193l5.333 2.667c0.658 0.329 1.46 0.062 1.789-0.596s0.062-1.46-0.596-1.789l-4.596-2.298c0 0 0-7.176 0-7.176 0-0.736-0.597-1.333-1.333-1.333s-1.333 0.597-1.333 1.333z" })]);
}
ss.compatConfig = { MODE: 3 };
function us() {
  return T(), F("svg", { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 32 32", fill: "currentColor", "aria-hidden": "true", class: "dp__icon" }, [L("path", { d: "M24.943 19.057l-8-8c-0.521-0.521-1.365-0.521-1.885 0l-8 8c-0.52 0.52-0.52 1.365 0 1.885s1.365 0.52 1.885 0l7.057-7.057c0 0 7.057 7.057 7.057 7.057 0.52 0.52 1.365 0.52 1.885 0s0.52-1.365 0-1.885z" })]);
}
us.compatConfig = { MODE: 3 };
function cs() {
  return T(), F("svg", { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 32 32", fill: "currentColor", "aria-hidden": "true", class: "dp__icon" }, [L("path", { d: "M7.057 12.943l8 8c0.521 0.521 1.365 0.521 1.885 0l8-8c0.52-0.52 0.52-1.365 0-1.885s-1.365-0.52-1.885 0l-7.057 7.057c0 0-7.057-7.057-7.057-7.057-0.52-0.52-1.365-0.52-1.885 0s-0.52 1.365 0 1.885z" })]);
}
cs.compatConfig = { MODE: 3 };
const Xt = (e, t) => t ? new Date(e.toLocaleString("en-US", { timeZone: t })) : new Date(e), ds = (e, t, n) => Ci(e, t, n) || de(), mg = (e, t, n) => {
  const a = t.dateInTz ? Xt(new Date(e), t.dateInTz) : de(e);
  return n ? Wt(a, true) : a;
}, Ci = (e, t, n) => {
  if (!e) return null;
  const a = n ? Wt(de(e), true) : de(e);
  return t ? t.exactMatch ? mg(e, t, n) : Xt(a, t.timezone) : a;
}, hg = (e) => {
  if (!e) return 0;
  const t = /* @__PURE__ */ new Date(), n = new Date(t.toLocaleString("en-US", { timeZone: "UTC" })), a = new Date(t.toLocaleString("en-US", { timeZone: e })), r = a.getTimezoneOffset() / 60;
  return (+n - +a) / (1e3 * 60 * 60) - r;
};
var dn = ((e) => (e.month = "month", e.year = "year", e))(dn || {}), $a = ((e) => (e.top = "top", e.bottom = "bottom", e))($a || {}), Ia = ((e) => (e.header = "header", e.calendar = "calendar", e.timePicker = "timePicker", e))(Ia || {}), Nt = ((e) => (e.month = "month", e.year = "year", e.calendar = "calendar", e.time = "time", e.minutes = "minutes", e.hours = "hours", e.seconds = "seconds", e))(Nt || {});
const gg = ["timestamp", "date", "iso"];
var zt = ((e) => (e.up = "up", e.down = "down", e.left = "left", e.right = "right", e))(zt || {}), it = ((e) => (e.arrowUp = "ArrowUp", e.arrowDown = "ArrowDown", e.arrowLeft = "ArrowLeft", e.arrowRight = "ArrowRight", e.enter = "Enter", e.space = " ", e.esc = "Escape", e.tab = "Tab", e.home = "Home", e.end = "End", e.pageUp = "PageUp", e.pageDown = "PageDown", e))(it || {});
function Fu(e) {
  return (t) => new Intl.DateTimeFormat(e, { weekday: "short", timeZone: "UTC" }).format(/* @__PURE__ */ new Date(`2017-01-0${t}T00:00:00+00:00`)).slice(0, 2);
}
function wg(e) {
  return (t) => Ln(Xt(/* @__PURE__ */ new Date(`2017-01-0${t}T00:00:00+00:00`), "UTC"), "EEEEEE", { locale: e });
}
const yg = (e, t, n) => {
  const a = [1, 2, 3, 4, 5, 6, 7];
  let r;
  if (e !== null) try {
    r = a.map(wg(e));
  } catch {
    r = a.map(Fu(t));
  }
  else r = a.map(Fu(t));
  const l = r.slice(0, n), o = r.slice(n + 1, r.length);
  return [r[n]].concat(...o).concat(...l);
}, ps = (e, t, n) => {
  const a = [];
  for (let r = +e[0]; r <= +e[1]; r++) a.push({ value: +r, text: vp(r, t) });
  return n ? a.reverse() : a;
}, dp = (e, t, n) => {
  const a = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12].map((l) => {
    const o = l < 10 ? `0${l}` : l;
    return /* @__PURE__ */ new Date(`2017-${o}-01T00:00:00+00:00`);
  });
  if (e !== null) try {
    const l = n === "long" ? "LLLL" : "LLL";
    return a.map((o, i) => {
      const s = Ln(Xt(o, "UTC"), l, { locale: e });
      return { text: s.charAt(0).toUpperCase() + s.substring(1), value: i };
    });
  } catch {
  }
  const r = new Intl.DateTimeFormat(t, { month: n, timeZone: "UTC" });
  return a.map((l, o) => {
    const i = r.format(l);
    return { text: i.charAt(0).toUpperCase() + i.substring(1), value: o };
  });
}, bg = (e) => [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11][e], kt = (e) => {
  const t = f(e);
  return t != null && t.$el ? t == null ? void 0 : t.$el : t;
}, _g = (e) => ({ type: "dot", ...e ?? {} }), pp = (e) => Array.isArray(e) ? !!e[0] && !!e[1] : false, fs = { prop: (e) => `"${e}" prop must be enabled!`, dateArr: (e) => `You need to use array as "model-value" binding in order to support "${e}"` }, Tt = (e) => e, Yu = (e) => e === 0 ? e : !e || isNaN(+e) ? null : +e, qu = (e) => e === null, fp = (e) => {
  if (e) return [...e.querySelectorAll("input, button, select, textarea, a[href]")][0];
}, xg = (e) => {
  const t = [], n = (a) => a.filter((r) => r);
  for (let a = 0; a < e.length; a += 3) {
    const r = [e[a], e[a + 1], e[a + 2]];
    t.push(n(r));
  }
  return t;
}, tl = (e, t, n) => {
  const a = n != null, r = t != null;
  if (!a && !r) return false;
  const l = +n, o = +t;
  return a && r ? +e > l || +e < o : a ? +e > l : r ? +e < o : false;
}, fr = (e, t) => xg(e).map((n) => n.map((a) => {
  const { active: r, disabled: l, isBetween: o, highlighted: i } = t(a);
  return { ...a, active: r, disabled: l, className: { dp__overlay_cell_active: r, dp__overlay_cell: !r, dp__overlay_cell_disabled: l, dp__overlay_cell_pad: true, dp__overlay_cell_active_disabled: l && r, dp__cell_in_between: o, "dp--highlighted": i } };
})), pa = (e, t, n = false) => {
  e && t.allowStopPropagation && (n && e.stopImmediatePropagation(), e.stopPropagation());
}, kg = () => ["a[href]", "area[href]", "input:not([disabled]):not([type='hidden'])", "select:not([disabled])", "textarea:not([disabled])", "button:not([disabled])", "[tabindex]:not([tabindex='-1'])", "[data-datepicker-instance]"].join(", ");
function Sg(e, t) {
  let n = [...document.querySelectorAll(kg())];
  n = n.filter((r) => !e.contains(r) || r.hasAttribute("data-datepicker-instance"));
  const a = n.indexOf(e);
  if (a >= 0 && (t ? a - 1 >= 0 : a + 1 <= n.length)) return n[a + (t ? -1 : 1)];
}
const Cg = (e, t) => e == null ? void 0 : e.querySelector(`[data-dp-element="${t}"]`), vp = (e, t) => new Intl.NumberFormat(t, { useGrouping: false, style: "decimal" }).format(e), vs = (e) => Ln(e, "dd-MM-yyyy"), qo = (e) => Array.isArray(e), Hl = (e, t) => t.get(vs(e)), Mg = (e, t) => e ? t ? t instanceof Map ? !!Hl(e, t) : t(de(e)) : false : true, Qt = (e, t, n = false) => {
  if (e.key === it.enter || e.key === it.space) return n && e.preventDefault(), t();
}, zu = (e, t, n, a, r, l) => {
  const o = Si(e, t.slice(0, e.length), /* @__PURE__ */ new Date(), { locale: l });
  return Yr(o) && Zd(o) ? a || r ? o : Je(o, { hours: +n.hours, minutes: +(n == null ? void 0 : n.minutes), seconds: +(n == null ? void 0 : n.seconds), milliseconds: 0 }) : null;
}, Tg = (e, t, n, a, r, l) => {
  const o = Array.isArray(n) ? n[0] : n;
  if (typeof t == "string") return zu(e, t, o, a, r, l);
  if (Array.isArray(t)) {
    let i = null;
    for (const s of t) if (i = zu(e, s, o, a, r, l), i) break;
    return i;
  }
  return typeof t == "function" ? t(e) : null;
}, de = (e) => e ? new Date(e) : /* @__PURE__ */ new Date(), Ag = (e, t, n) => {
  if (t) {
    const r = (e.getMonth() + 1).toString().padStart(2, "0"), l = e.getDate().toString().padStart(2, "0"), o = e.getHours().toString().padStart(2, "0"), i = e.getMinutes().toString().padStart(2, "0"), s = n ? e.getSeconds().toString().padStart(2, "0") : "00";
    return `${e.getFullYear()}-${r}-${l}T${o}:${i}:${s}.000Z`;
  }
  const a = Date.UTC(e.getUTCFullYear(), e.getUTCMonth(), e.getUTCDate(), e.getUTCHours(), e.getUTCMinutes(), e.getUTCSeconds());
  return new Date(a).toISOString();
}, Wt = (e, t) => {
  const n = de(JSON.parse(JSON.stringify(e))), a = Je(n, { hours: 0, minutes: 0, seconds: 0, milliseconds: 0 });
  return t ? _m(a) : a;
}, fa = (e, t, n, a) => {
  let r = e ? de(e) : de();
  return (t || t === 0) && (r = pg(r, +t)), (n || n === 0) && (r = fg(r, +n)), (a || a === 0) && (r = sp(r, +a)), ip(r, 0);
}, pt = (e, t) => !e || !t ? false : el(Wt(e), Wt(t)), qe = (e, t) => !e || !t ? false : er(Wt(e), Wt(t)), gt = (e, t) => !e || !t ? false : dr(Wt(e), Wt(t)), fo = (e, t, n) => e != null && e[0] && e != null && e[1] ? gt(n, e[0]) && pt(n, e[1]) : e != null && e[0] && t ? gt(n, e[0]) && pt(n, t) || pt(n, e[0]) && gt(n, t) : false, fn = (e) => {
  const t = Je(new Date(e), { date: 1 });
  return Wt(t);
}, zo = (e, t, n) => t && (n || n === 0) ? Object.fromEntries(["hours", "minutes", "seconds"].map((a) => a === t ? [a, n] : [a, isNaN(+e[a]) ? void 0 : +e[a]])) : { hours: isNaN(+e.hours) ? void 0 : +e.hours, minutes: isNaN(+e.minutes) ? void 0 : +e.minutes, seconds: isNaN(+e.seconds) ? void 0 : +e.seconds }, Va = (e) => ({ hours: Wn(e), minutes: ha(e), seconds: cr(e) }), mp = (e, t) => {
  if (t) {
    const n = Re(de(t));
    if (n > e) return 12;
    if (n === e) return Be(de(t));
  }
}, hp = (e, t) => {
  if (t) {
    const n = Re(de(t));
    return n < e ? -1 : n === e ? Be(de(t)) : void 0;
  }
}, vr = (e) => {
  if (e) return Re(de(e));
}, gp = (e, t) => {
  const n = gt(e, t) ? t : e, a = gt(t, e) ? t : e;
  return Wd({ start: n, end: a });
}, Dg = (e) => {
  const t = hn(e, 1);
  return { month: Be(t), year: Re(t) };
}, jn = (e, t) => {
  const n = bn(e, { weekStartsOn: +t }), a = Gd(e, { weekStartsOn: +t });
  return [n, a];
}, wp = (e, t) => {
  const n = { hours: Wn(de()), minutes: ha(de()), seconds: t ? cr(de()) : 0 };
  return Object.assign(n, e);
}, ua = (e, t, n) => [Je(de(e), { date: 1 }), Je(de(), { month: t, year: n, date: 1 })], Fn = (e, t, n) => {
  let a = e ? de(e) : de();
  return (t || t === 0) && (a = op(a, t)), n && (a = An(a, n)), a;
}, yp = (e, t, n, a, r) => {
  if (!a || r && !t || !r && !n) return false;
  const l = r ? hn(e, 1) : pr(e, 1), o = [Be(l), Re(l)];
  return r ? !Og(...o, t) : !Lg(...o, n);
}, Lg = (e, t, n) => pt(...ua(n, e, t)) || qe(...ua(n, e, t)), Og = (e, t, n) => gt(...ua(n, e, t)) || qe(...ua(n, e, t)), bp = (e, t, n, a, r, l, o) => {
  if (typeof t == "function" && !o) return t(e);
  const i = n ? { locale: n } : void 0;
  return Array.isArray(e) ? `${Ln(e[0], l, i)}${r && !e[1] ? "" : a}${e[1] ? Ln(e[1], l, i) : ""}` : Ln(e, l, i);
}, Ga = (e) => {
  if (e) return null;
  throw new Error(fs.prop("partial-range"));
}, Tl = (e, t) => {
  if (t) return e();
  throw new Error(fs.prop("range"));
}, Mi = (e) => Array.isArray(e) ? Yr(e[0]) && (e[1] ? Yr(e[1]) : true) : e ? Yr(e) : false, Pg = (e, t) => Je(t ?? de(), { hours: +e.hours || 0, minutes: +e.minutes || 0, seconds: +e.seconds || 0 }), Ho = (e, t, n, a) => {
  if (!e) return true;
  if (a) {
    const r = n === "max" ? el(e, t) : dr(e, t), l = { seconds: 0, milliseconds: 0 };
    return r || er(Je(e, l), Je(t, l));
  }
  return n === "max" ? e.getTime() <= t.getTime() : e.getTime() >= t.getTime();
}, Ko = (e, t, n) => e ? Pg(e, t) : de(n ?? t), Hu = (e, t, n, a, r) => {
  if (Array.isArray(a)) {
    const o = Ko(e, a[0], t), i = Ko(e, a[1], t);
    return Ho(a[0], o, n, !!t) && Ho(a[1], i, n, !!t) && r;
  }
  const l = Ko(e, a, t);
  return Ho(a, l, n, !!t) && r;
}, Zo = (e) => Je(de(), Va(e)), $g = (e, t) => e instanceof Map ? Array.from(e.values()).filter((n) => Re(de(n)) === t).map((n) => Be(n)) : [], _p = (e, t, n) => typeof e == "function" ? e({ month: t, year: n }) : !!e.months.find((a) => a.month === t && a.year === n), ms = (e, t) => typeof e == "function" ? e(t) : e.years.includes(t), xp = (e) => Ln(e, "yyyy-MM-dd"), Dr = un({ menuFocused: false, shiftKeyInMenu: false }), kp = () => {
  const e = (n) => {
    Dr.menuFocused = n;
  }, t = (n) => {
    Dr.shiftKeyInMenu !== n && (Dr.shiftKeyInMenu = n);
  };
  return { control: J(() => ({ shiftKeyInMenu: Dr.shiftKeyInMenu, menuFocused: Dr.menuFocused })), setMenuFocused: e, setShiftKey: t };
}, nt = un({ monthYear: [], calendar: [], time: [], actionRow: [], selectionGrid: [], timePicker: { 0: [], 1: [] }, monthPicker: [] }), Wo = te(null), Al = te(false), Uo = te(false), Go = te(false), Qo = te(false), Et = te(0), ht = te(0), ka = () => {
  const e = J(() => Al.value ? [...nt.selectionGrid, nt.actionRow].filter((u) => u.length) : Uo.value ? [...nt.timePicker[0], ...nt.timePicker[1], Qo.value ? [] : [Wo.value], nt.actionRow].filter((u) => u.length) : Go.value ? [...nt.monthPicker, nt.actionRow] : [nt.monthYear, ...nt.calendar, nt.time, nt.actionRow].filter((u) => u.length)), t = (u) => {
    Et.value = u ? Et.value + 1 : Et.value - 1;
    let p = null;
    e.value[ht.value] && (p = e.value[ht.value][Et.value]), !p && e.value[ht.value + (u ? 1 : -1)] ? (ht.value = ht.value + (u ? 1 : -1), Et.value = u ? 0 : e.value[ht.value].length - 1) : p || (Et.value = u ? Et.value - 1 : Et.value + 1);
  }, n = (u) => {
    ht.value === 0 && !u || ht.value === e.value.length && u || (ht.value = u ? ht.value + 1 : ht.value - 1, e.value[ht.value] ? e.value[ht.value] && !e.value[ht.value][Et.value] && Et.value !== 0 && (Et.value = e.value[ht.value].length - 1) : ht.value = u ? ht.value - 1 : ht.value + 1);
  }, a = (u) => {
    let p = null;
    e.value[ht.value] && (p = e.value[ht.value][Et.value]), p ? p.focus({ preventScroll: !Al.value }) : Et.value = u ? Et.value - 1 : Et.value + 1;
  }, r = () => {
    t(true), a(true);
  }, l = () => {
    t(false), a(false);
  }, o = () => {
    n(false), a(true);
  }, i = () => {
    n(true), a(true);
  }, s = (u, p) => {
    nt[p] = u;
  }, c = (u, p) => {
    nt[p] = u;
  }, d = () => {
    Et.value = 0, ht.value = 0;
  };
  return { buildMatrix: s, buildMultiLevelMatrix: c, setTimePickerBackRef: (u) => {
    Wo.value = u;
  }, setSelectionGrid: (u) => {
    Al.value = u, d(), u || (nt.selectionGrid = []);
  }, setTimePicker: (u, p = false) => {
    Uo.value = u, Qo.value = p, d(), u || (nt.timePicker[0] = [], nt.timePicker[1] = []);
  }, setTimePickerElements: (u, p = 0) => {
    nt.timePicker[p] = u;
  }, arrowRight: r, arrowLeft: l, arrowUp: o, arrowDown: i, clearArrowNav: () => {
    nt.monthYear = [], nt.calendar = [], nt.time = [], nt.actionRow = [], nt.selectionGrid = [], nt.timePicker[0] = [], nt.timePicker[1] = [], Al.value = false, Uo.value = false, Qo.value = false, Go.value = false, d(), Wo.value = null;
  }, setMonthPicker: (u) => {
    Go.value = u, d();
  }, refSets: nt };
}, Ku = (e) => ({ menuAppearTop: "dp-menu-appear-top", menuAppearBottom: "dp-menu-appear-bottom", open: "dp-slide-down", close: "dp-slide-up", next: "calendar-next", previous: "calendar-prev", vNext: "dp-slide-up", vPrevious: "dp-slide-down", ...e ?? {} }), Rg = (e) => ({ toggleOverlay: "Toggle overlay", menu: "Datepicker menu", input: "Datepicker input", calendarWrap: "Calendar wrapper", calendarDays: "Calendar days", openTimePicker: "Open time picker", closeTimePicker: "Close time Picker", incrementValue: (t) => `Increment ${t}`, decrementValue: (t) => `Decrement ${t}`, openTpOverlay: (t) => `Open ${t} overlay`, amPmButton: "Switch AM/PM mode", openYearsOverlay: "Open years overlay", openMonthsOverlay: "Open months overlay", nextMonth: "Next month", prevMonth: "Previous month", nextYear: "Next year", prevYear: "Previous year", day: void 0, weekDay: void 0, ...e ?? {} }), Zu = (e) => e ? typeof e == "boolean" ? e ? 2 : 0 : +e >= 2 ? +e : 2 : 0, Eg = (e) => {
  const t = typeof e == "object" && e, n = { static: true, solo: false };
  if (!e) return { ...n, count: Zu(false) };
  const a = t ? e : {}, r = t ? a.count ?? true : e, l = Zu(r);
  return Object.assign(n, a, { count: l });
}, Ng = (e, t, n) => e || (typeof n == "string" ? n : t), Ig = (e) => typeof e == "boolean" ? e ? Ku({}) : false : Ku(e), Vg = (e) => {
  const t = { enterSubmit: true, tabSubmit: true, openMenu: true, selectOnFocus: false, rangeSeparator: " - " };
  return typeof e == "object" ? { ...t, ...e ?? {}, enabled: true } : { ...t, enabled: e };
}, jg = (e) => ({ months: [], years: [], times: { hours: [], minutes: [], seconds: [] }, ...e ?? {} }), Bg = (e) => ({ showSelect: true, showCancel: true, showNow: false, showPreview: true, ...e ?? {} }), Fg = (e) => {
  const t = { input: false };
  return typeof e == "object" ? { ...t, ...e ?? {}, enabled: true } : { enabled: e, ...t };
}, Yg = (e) => ({ allowStopPropagation: true, closeOnScroll: false, modeHeight: 255, allowPreventDefault: false, closeOnClearValue: true, closeOnAutoApply: true, noSwipe: false, keepActionRow: false, onClickOutside: void 0, tabOutClosesMenu: true, arrowLeft: void 0, keepViewOnOffsetClick: false, timeArrowHoldThreshold: 0, ...e ?? {} }), qg = (e) => {
  const t = { dates: Array.isArray(e) ? e.map((n) => de(n)) : [], years: [], months: [], quarters: [], weeks: [], weekdays: [], options: { highlightDisabled: false } };
  return typeof e == "function" ? e : { ...t, ...e ?? {} };
}, zg = (e) => typeof e == "object" ? { type: (e == null ? void 0 : e.type) ?? "local", hideOnOffsetDates: (e == null ? void 0 : e.hideOnOffsetDates) ?? false } : { type: e, hideOnOffsetDates: false }, Hg = (e, t) => typeof e == "object" ? { enabled: true, noDisabledRange: false, showLastInRange: true, minMaxRawRange: false, partialRange: true, disableTimeRangeValidation: false, maxRange: void 0, minRange: void 0, autoRange: void 0, fixedStart: false, fixedEnd: false, ...e } : { enabled: e, noDisabledRange: t.noDisabledRange, showLastInRange: t.showLastInRange, minMaxRawRange: t.minMaxRawRange, partialRange: t.partialRange, disableTimeRangeValidation: t.disableTimeRangeValidation, maxRange: t.maxRange, minRange: t.minRange, autoRange: t.autoRange, fixedStart: t.fixedStart, fixedEnd: t.fixedEnd }, Kg = (e, t) => e ? typeof e == "string" ? { timezone: e, exactMatch: false, dateInTz: void 0, emitTimezone: t, convertModel: true } : { timezone: e.timezone, exactMatch: e.exactMatch ?? false, dateInTz: e.dateInTz ?? void 0, emitTimezone: t ?? e.emitTimezone, convertModel: e.convertModel ?? true } : { timezone: void 0, exactMatch: false, emitTimezone: t }, Xo = (e, t, n) => new Map(e.map((a) => {
  const r = ds(a, t, n);
  return [vs(r), r];
})), Zg = (e, t) => e.length ? new Map(e.map((n) => {
  const a = ds(n.date, t);
  return [vs(a), n];
})) : null, Wg = (e) => {
  var t;
  return { minDate: Ci(e.minDate, e.timezone, e.isSpecific), maxDate: Ci(e.maxDate, e.timezone, e.isSpecific), disabledDates: qo(e.disabledDates) ? Xo(e.disabledDates, e.timezone, e.isSpecific) : e.disabledDates, allowedDates: qo(e.allowedDates) ? Xo(e.allowedDates, e.timezone, e.isSpecific) : null, highlight: typeof e.highlight == "object" && qo((t = e.highlight) == null ? void 0 : t.dates) ? Xo(e.highlight.dates, e.timezone) : e.highlight, markers: Zg(e.markers, e.timezone) };
}, Ug = (e, t) => typeof e == "boolean" ? { enabled: e, dragSelect: true, limit: +t } : { enabled: !!e, limit: e.limit ? +e.limit : null, dragSelect: e.dragSelect ?? true }, Gg = (e) => ({ ...Object.fromEntries(Object.keys(e).map((t) => {
  const n = t, a = e[n], r = typeof e[n] == "string" ? { [a]: true } : Object.fromEntries(a.map((l) => [l, true]));
  return [t, r];
})) }), ct = (e) => {
  const t = () => {
    const M = e.enableSeconds ? ":ss" : "", C = e.enableMinutes ? ":mm" : "";
    return e.is24 ? `HH${C}${M}` : `hh${C}${M} aa`;
  }, n = () => {
    var M;
    return e.format ? e.format : e.monthPicker ? "MM/yyyy" : e.timePicker ? t() : e.weekPicker ? `${((M = N.value) == null ? void 0 : M.type) === "iso" ? "RR" : "ww"}-yyyy` : e.yearPicker ? "yyyy" : e.quarterPicker ? "QQQ/yyyy" : e.enableTimePicker ? `MM/dd/yyyy, ${t()}` : "MM/dd/yyyy";
  }, a = (M) => wp(M, e.enableSeconds), r = () => g.value.enabled ? e.startTime && Array.isArray(e.startTime) ? [a(e.startTime[0]), a(e.startTime[1])] : null : e.startTime && !Array.isArray(e.startTime) ? a(e.startTime) : null, l = J(() => Eg(e.multiCalendars)), o = J(() => r()), i = J(() => Rg(e.ariaLabels)), s = J(() => jg(e.filters)), c = J(() => Ig(e.transitions)), d = J(() => Bg(e.actionRow)), u = J(() => Ng(e.previewFormat, e.format, n())), p = J(() => Vg(e.textInput)), v = J(() => Fg(e.inline)), b = J(() => Yg(e.config)), h = J(() => qg(e.highlight)), N = J(() => zg(e.weekNumbers)), I = J(() => Kg(e.timezone, e.emitTimezone)), x = J(() => Ug(e.multiDates, e.multiDatesLimit)), _ = J(() => Wg({ minDate: e.minDate, maxDate: e.maxDate, disabledDates: e.disabledDates, allowedDates: e.allowedDates, highlight: h.value, markers: e.markers, timezone: I.value, isSpecific: e.monthPicker || e.yearPicker || e.quarterPicker })), g = J(() => Hg(e.range, { minMaxRawRange: false, maxRange: e.maxRange, minRange: e.minRange, noDisabledRange: e.noDisabledRange, showLastInRange: e.showLastInRange, partialRange: e.partialRange, disableTimeRangeValidation: e.disableTimeRangeValidation, autoRange: e.autoRange, fixedStart: e.fixedStart, fixedEnd: e.fixedEnd })), R = J(() => Gg(e.ui));
  return { defaultedTransitions: c, defaultedMultiCalendars: l, defaultedStartTime: o, defaultedAriaLabels: i, defaultedFilters: s, defaultedActionRow: d, defaultedPreviewFormat: u, defaultedTextInput: p, defaultedInline: v, defaultedConfig: b, defaultedHighlight: h, defaultedWeekNumbers: N, defaultedRange: g, propDates: _, defaultedTz: I, defaultedMultiDates: x, defaultedUI: R, getDefaultPattern: n, getDefaultStartTime: r };
}, Qg = (e, t, n) => {
  const a = te(), { defaultedTextInput: r, defaultedRange: l, defaultedTz: o, defaultedMultiDates: i, getDefaultPattern: s } = ct(t), c = te(""), d = ir(t, "format"), u = ir(t, "formatLocale");
  He(a, () => {
    typeof t.onInternalModelChange == "function" && e("internal-model-change", a.value, G(true));
  }, { deep: true }), He(l, (D, fe) => {
    D.enabled !== fe.enabled && (a.value = null);
  }), He(d, () => {
    ae();
  });
  const p = (D) => o.value.timezone && o.value.convertModel ? Xt(D, o.value.timezone) : D, v = (D) => {
    if (o.value.timezone && o.value.convertModel) {
      const fe = hg(o.value.timezone);
      return vm(D, fe);
    }
    return D;
  }, b = (D, fe, Ae = false) => bp(D, t.format, t.formatLocale, r.value.rangeSeparator, t.modelAuto, fe ?? s(), Ae), h = (D) => D ? t.modelType ? ve(D) : { hours: Wn(D), minutes: ha(D), seconds: t.enableSeconds ? cr(D) : 0 } : null, N = (D) => t.modelType ? ve(D) : { month: Be(D), year: Re(D) }, I = (D) => Array.isArray(D) ? i.value.enabled ? D.map((fe) => x(fe, An(de(), fe))) : Tl(() => [An(de(), D[0]), D[1] ? An(de(), D[1]) : Ga(l.value.partialRange)], l.value.enabled) : An(de(), +D), x = (D, fe) => (typeof D == "string" || typeof D == "number") && t.modelType ? ce(D) : fe, _ = (D) => Array.isArray(D) ? [x(D[0], fa(null, +D[0].hours, +D[0].minutes, D[0].seconds)), x(D[1], fa(null, +D[1].hours, +D[1].minutes, D[1].seconds))] : x(D, fa(null, D.hours, D.minutes, D.seconds)), g = (D) => {
    const fe = Je(de(), { date: 1 });
    return Array.isArray(D) ? i.value.enabled ? D.map((Ae) => x(Ae, Fn(fe, +Ae.month, +Ae.year))) : Tl(() => [x(D[0], Fn(fe, +D[0].month, +D[0].year)), x(D[1], D[1] ? Fn(fe, +D[1].month, +D[1].year) : Ga(l.value.partialRange))], l.value.enabled) : x(D, Fn(fe, +D.month, +D.year));
  }, R = (D) => {
    if (Array.isArray(D)) return D.map((fe) => ce(fe));
    throw new Error(fs.dateArr("multi-dates"));
  }, M = (D) => {
    if (Array.isArray(D) && l.value.enabled) {
      const fe = D[0], Ae = D[1];
      return [de(Array.isArray(fe) ? fe[0] : null), de(Array.isArray(Ae) ? Ae[0] : null)];
    }
    return de(D[0]);
  }, C = (D) => t.modelAuto ? Array.isArray(D) ? [ce(D[0]), ce(D[1])] : t.autoApply ? [ce(D)] : [ce(D), null] : Array.isArray(D) ? Tl(() => D[1] ? [ce(D[0]), D[1] ? ce(D[1]) : Ga(l.value.partialRange)] : [ce(D[0])], l.value.enabled) : ce(D), Y = () => {
    Array.isArray(a.value) && l.value.enabled && a.value.length === 1 && a.value.push(Ga(l.value.partialRange));
  }, P = () => {
    const D = a.value;
    return [ve(D[0]), D[1] ? ve(D[1]) : Ga(l.value.partialRange)];
  }, $ = () => a.value[1] ? P() : ve(Tt(a.value[0])), H = () => (a.value || []).map((D) => ve(D)), z = (D = false) => (D || Y(), t.modelAuto ? $() : i.value.enabled ? H() : Array.isArray(a.value) ? Tl(() => P(), l.value.enabled) : ve(Tt(a.value))), se = (D) => !D || Array.isArray(D) && !D.length ? null : t.timePicker ? _(Tt(D)) : t.monthPicker ? g(Tt(D)) : t.yearPicker ? I(Tt(D)) : i.value.enabled ? R(Tt(D)) : t.weekPicker ? M(Tt(D)) : C(Tt(D)), j = (D) => {
    const fe = se(D);
    Mi(Tt(fe)) ? (a.value = Tt(fe), ae()) : (a.value = null, c.value = "");
  }, y = () => {
    const D = (fe) => Ln(fe, r.value.format);
    return `${D(a.value[0])} ${r.value.rangeSeparator} ${a.value[1] ? D(a.value[1]) : ""}`;
  }, V = () => n.value && a.value ? Array.isArray(a.value) ? y() : Ln(a.value, r.value.format) : b(a.value), w = () => a.value ? i.value.enabled ? a.value.map((D) => b(D)).join("; ") : r.value.enabled && typeof r.value.format == "string" ? V() : b(a.value) : "", ae = () => {
    !t.format || typeof t.format == "string" || r.value.enabled && typeof r.value.format == "string" ? c.value = w() : c.value = t.format(a.value);
  }, ce = (D) => {
    if (t.utc) {
      const fe = new Date(D);
      return t.utc === "preserve" ? new Date(fe.getTime() + fe.getTimezoneOffset() * 6e4) : fe;
    }
    return t.modelType ? gg.includes(t.modelType) ? p(new Date(D)) : t.modelType === "format" && (typeof t.format == "string" || !t.format) ? p(Si(D, s(), /* @__PURE__ */ new Date(), { locale: u.value })) : p(Si(D, t.modelType, /* @__PURE__ */ new Date(), { locale: u.value })) : p(new Date(D));
  }, ve = (D) => D ? t.utc ? Ag(D, t.utc === "preserve", t.enableSeconds) : t.modelType ? t.modelType === "timestamp" ? +v(D) : t.modelType === "iso" ? v(D).toISOString() : t.modelType === "format" && (typeof t.format == "string" || !t.format) ? b(v(D)) : b(v(D), t.modelType, true) : v(D) : "", _e = (D, fe = false, Ae = false) => {
    if (Ae) return D;
    if (e("update:model-value", D), o.value.emitTimezone && fe) {
      const re = Array.isArray(D) ? D.map((Oe) => Xt(Tt(Oe), o.value.emitTimezone)) : Xt(Tt(D), o.value.emitTimezone);
      e("update:model-timezone-value", re);
    }
  }, q = (D) => Array.isArray(a.value) ? i.value.enabled ? a.value.map((fe) => D(fe)) : [D(a.value[0]), a.value[1] ? D(a.value[1]) : Ga(l.value.partialRange)] : D(Tt(a.value)), oe = () => {
    if (Array.isArray(a.value)) {
      const D = jn(a.value[0], t.weekStart), fe = a.value[1] ? jn(a.value[1], t.weekStart) : [];
      return [D.map((Ae) => de(Ae)), fe.map((Ae) => de(Ae))];
    }
    return jn(a.value, t.weekStart).map((D) => de(D));
  }, S = (D, fe) => _e(Tt(q(D)), false, fe), W = (D) => {
    const fe = oe();
    return D ? fe : e("update:model-value", oe());
  }, G = (D = false) => (D || ae(), t.monthPicker ? S(N, D) : t.timePicker ? S(h, D) : t.yearPicker ? S(Re, D) : t.weekPicker ? W(D) : _e(z(D), true, D));
  return { inputValue: c, internalModelValue: a, checkBeforeEmit: () => a.value ? l.value.enabled ? l.value.partialRange ? a.value.length >= 1 : a.value.length === 2 : !!a.value : false, parseExternalModelValue: j, formatInputValue: ae, emitModelValue: G };
}, Xg = (e, t) => {
  const { defaultedFilters: n, propDates: a } = ct(e), { validateMonthYearInRange: r } = Sa(e), l = (d, u) => {
    let p = d;
    return n.value.months.includes(Be(p)) ? (p = u ? hn(d, 1) : pr(d, 1), l(p, u)) : p;
  }, o = (d, u) => {
    let p = d;
    return n.value.years.includes(Re(p)) ? (p = u ? es(d, 1) : up(d, 1), o(p, u)) : p;
  }, i = (d, u = false) => {
    const p = Je(de(), { month: e.month, year: e.year });
    let v = d ? hn(p, 1) : pr(p, 1);
    e.disableYearSelect && (v = An(v, e.year));
    let b = Be(v), h = Re(v);
    n.value.months.includes(b) && (v = l(v, d), b = Be(v), h = Re(v)), n.value.years.includes(h) && (v = o(v, d), h = Re(v)), r(b, h, d, e.preventMinMaxNavigation) && s(b, h, u);
  }, s = (d, u, p) => {
    t("update-month-year", { month: d, year: u, fromNav: p });
  }, c = J(() => (d) => yp(Je(de(), { month: e.month, year: e.year }), a.value.maxDate, a.value.minDate, e.preventMinMaxNavigation, d));
  return { handleMonthYearChange: i, isDisabled: c, updateMonthYear: s };
}, vo = { multiCalendars: { type: [Boolean, Number, String, Object], default: void 0 }, modelValue: { type: [String, Date, Array, Object, Number], default: null }, modelType: { type: String, default: null }, position: { type: String, default: "center" }, dark: { type: Boolean, default: false }, format: { type: [String, Function], default: () => null }, autoPosition: { type: Boolean, default: true }, altPosition: { type: Function, default: null }, transitions: { type: [Boolean, Object], default: true }, formatLocale: { type: Object, default: null }, utc: { type: [Boolean, String], default: false }, ariaLabels: { type: Object, default: () => ({}) }, offset: { type: [Number, String], default: 10 }, hideNavigation: { type: Array, default: () => [] }, timezone: { type: [String, Object], default: null }, emitTimezone: { type: String, default: null }, vertical: { type: Boolean, default: false }, disableMonthYearSelect: { type: Boolean, default: false }, disableYearSelect: { type: Boolean, default: false }, menuClassName: { type: String, default: null }, dayClass: { type: Function, default: null }, yearRange: { type: Array, default: () => [1900, 2100] }, calendarCellClassName: { type: String, default: null }, enableTimePicker: { type: Boolean, default: true }, autoApply: { type: Boolean, default: false }, disabledDates: { type: [Array, Function], default: () => [] }, monthNameFormat: { type: String, default: "short" }, startDate: { type: [Date, String], default: null }, startTime: { type: [Object, Array], default: null }, hideOffsetDates: { type: Boolean, default: false }, autoRange: { type: [Number, String], default: null }, noToday: { type: Boolean, default: false }, disabledWeekDays: { type: Array, default: () => [] }, allowedDates: { type: Array, default: null }, nowButtonLabel: { type: String, default: "Now" }, markers: { type: Array, default: () => [] }, escClose: { type: Boolean, default: true }, spaceConfirm: { type: Boolean, default: true }, monthChangeOnArrows: { type: Boolean, default: true }, presetDates: { type: Array, default: () => [] }, flow: { type: Array, default: () => [] }, partialFlow: { type: Boolean, default: false }, preventMinMaxNavigation: { type: Boolean, default: false }, minRange: { type: [Number, String], default: null }, maxRange: { type: [Number, String], default: null }, multiDatesLimit: { type: [Number, String], default: null }, reverseYears: { type: Boolean, default: false }, weekPicker: { type: Boolean, default: false }, filters: { type: Object, default: () => ({}) }, arrowNavigation: { type: Boolean, default: false }, disableTimeRangeValidation: { type: Boolean, default: false }, highlight: { type: [Function, Object], default: null }, teleport: { type: [Boolean, String, Object], default: null }, teleportCenter: { type: Boolean, default: false }, locale: { type: String, default: "en-Us" }, weekNumName: { type: String, default: "W" }, weekStart: { type: [Number, String], default: 1 }, weekNumbers: { type: [String, Function, Object], default: null }, calendarClassName: { type: String, default: null }, monthChangeOnScroll: { type: [Boolean, String], default: true }, dayNames: { type: [Function, Array], default: null }, monthPicker: { type: Boolean, default: false }, customProps: { type: Object, default: null }, yearPicker: { type: Boolean, default: false }, modelAuto: { type: Boolean, default: false }, selectText: { type: String, default: "Select" }, cancelText: { type: String, default: "Cancel" }, previewFormat: { type: [String, Function], default: () => "" }, multiDates: { type: [Object, Boolean], default: false }, partialRange: { type: Boolean, default: true }, ignoreTimeValidation: { type: Boolean, default: false }, minDate: { type: [Date, String], default: null }, maxDate: { type: [Date, String], default: null }, minTime: { type: Object, default: null }, maxTime: { type: Object, default: null }, name: { type: String, default: null }, placeholder: { type: String, default: "" }, hideInputIcon: { type: Boolean, default: false }, clearable: { type: Boolean, default: true }, state: { type: Boolean, default: null }, required: { type: Boolean, default: false }, autocomplete: { type: String, default: "off" }, inputClassName: { type: String, default: null }, fixedStart: { type: Boolean, default: false }, fixedEnd: { type: Boolean, default: false }, timePicker: { type: Boolean, default: false }, enableSeconds: { type: Boolean, default: false }, is24: { type: Boolean, default: true }, noHoursOverlay: { type: Boolean, default: false }, noMinutesOverlay: { type: Boolean, default: false }, noSecondsOverlay: { type: Boolean, default: false }, hoursGridIncrement: { type: [String, Number], default: 1 }, minutesGridIncrement: { type: [String, Number], default: 5 }, secondsGridIncrement: { type: [String, Number], default: 5 }, hoursIncrement: { type: [Number, String], default: 1 }, minutesIncrement: { type: [Number, String], default: 1 }, secondsIncrement: { type: [Number, String], default: 1 }, range: { type: [Boolean, Object], default: false }, uid: { type: String, default: null }, disabled: { type: Boolean, default: false }, readonly: { type: Boolean, default: false }, inline: { type: [Boolean, Object], default: false }, textInput: { type: [Boolean, Object], default: false }, noDisabledRange: { type: Boolean, default: false }, sixWeeks: { type: [Boolean, String], default: false }, actionRow: { type: Object, default: () => ({}) }, focusStartDate: { type: Boolean, default: false }, disabledTimes: { type: [Function, Array], default: void 0 }, showLastInRange: { type: Boolean, default: true }, timePickerInline: { type: Boolean, default: false }, calendar: { type: Function, default: null }, config: { type: Object, default: void 0 }, quarterPicker: { type: Boolean, default: false }, yearFirst: { type: Boolean, default: false }, loading: { type: Boolean, default: false }, onInternalModelChange: { type: [Function, Object], default: null }, enableMinutes: { type: Boolean, default: true }, ui: { type: Object, default: () => ({}) } }, _n = { ...vo, shadow: { type: Boolean, default: false }, flowStep: { type: Number, default: 0 }, internalModelValue: { type: [Date, Array], default: null }, noOverlayFocus: { type: Boolean, default: false }, collapse: { type: Boolean, default: false }, menuWrapRef: { type: Object, default: null }, getInputRect: { type: Function, default: () => ({}) }, isTextInputDate: { type: Boolean, default: false } }, Jg = ["title"], ew = ["disabled"], tw = /* @__PURE__ */ Rt({ compatConfig: { MODE: 3 }, __name: "ActionRow", props: { menuMount: { type: Boolean, default: false }, calendarWidth: { type: Number, default: 0 }, ..._n }, emits: ["close-picker", "select-date", "select-now", "invalid-select"], setup(e, { emit: t }) {
  const n = t, a = e, { defaultedActionRow: r, defaultedPreviewFormat: l, defaultedMultiCalendars: o, defaultedTextInput: i, defaultedInline: s, defaultedRange: c, defaultedMultiDates: d, getDefaultPattern: u } = ct(a), { isTimeValid: p, isMonthValid: v } = Sa(a), { buildMatrix: b } = ka(), h = te(null), N = te(null), I = te(false), x = te({}), _ = te(null), g = te(null);
  ot(() => {
    a.arrowNavigation && b([kt(h), kt(N)], "actionRow"), R(), window.addEventListener("resize", R);
  }), br(() => {
    window.removeEventListener("resize", R);
  });
  const R = () => {
    I.value = false, setTimeout(() => {
      var y, V;
      const w = (y = _.value) == null ? void 0 : y.getBoundingClientRect(), ae = (V = g.value) == null ? void 0 : V.getBoundingClientRect();
      w && ae && (x.value.maxWidth = `${ae.width - w.width - 20}px`), I.value = true;
    }, 0);
  }, M = J(() => c.value.enabled && !c.value.partialRange && a.internalModelValue ? a.internalModelValue.length === 2 : true), C = J(() => !p.value(a.internalModelValue) || !v.value(a.internalModelValue) || !M.value), Y = () => {
    const y = l.value;
    return a.timePicker || a.monthPicker, y(Tt(a.internalModelValue));
  }, P = () => {
    const y = a.internalModelValue;
    return o.value.count > 0 ? `${$(y[0])} - ${$(y[1])}` : [$(y[0]), $(y[1])];
  }, $ = (y) => bp(y, l.value, a.formatLocale, i.value.rangeSeparator, a.modelAuto, u()), H = J(() => !a.internalModelValue || !a.menuMount ? "" : typeof l.value == "string" ? Array.isArray(a.internalModelValue) ? a.internalModelValue.length === 2 && a.internalModelValue[1] ? P() : d.value.enabled ? a.internalModelValue.map((y) => `${$(y)}`) : a.modelAuto ? `${$(a.internalModelValue[0])}` : `${$(a.internalModelValue[0])} -` : $(a.internalModelValue) : Y()), z = () => d.value.enabled ? "; " : " - ", se = J(() => Array.isArray(H.value) ? H.value.join(z()) : H.value), j = () => {
    p.value(a.internalModelValue) && v.value(a.internalModelValue) && M.value ? n("select-date") : n("invalid-select");
  };
  return (y, V) => (T(), F("div", { ref_key: "actionRowRef", ref: g, class: "dp__action_row" }, [y.$slots["action-row"] ? ye(y.$slots, "action-row", Ot(ft({ key: 0 }, { internalModelValue: y.internalModelValue, disabled: C.value, selectDate: () => y.$emit("select-date"), closePicker: () => y.$emit("close-picker") }))) : (T(), F(Ce, { key: 1 }, [f(r).showPreview ? (T(), F("div", { key: 0, class: "dp__selection_preview", title: se.value, style: Lt(x.value) }, [y.$slots["action-preview"] && I.value ? ye(y.$slots, "action-preview", { key: 0, value: y.internalModelValue }) : Z("", true), !y.$slots["action-preview"] && I.value ? (T(), F(Ce, { key: 1 }, [Ge(ge(se.value), 1)], 64)) : Z("", true)], 12, Jg)) : Z("", true), L("div", { ref_key: "actionBtnContainer", ref: _, class: "dp__action_buttons", "data-dp-element": "action-row" }, [y.$slots["action-buttons"] ? ye(y.$slots, "action-buttons", { key: 0, value: y.internalModelValue }) : Z("", true), y.$slots["action-buttons"] ? Z("", true) : (T(), F(Ce, { key: 1 }, [!f(s).enabled && f(r).showCancel ? (T(), F("button", { key: 0, ref_key: "cancelButtonRef", ref: h, type: "button", class: "dp__action_button dp__action_cancel", onClick: V[0] || (V[0] = (w) => y.$emit("close-picker")), onKeydown: V[1] || (V[1] = (w) => f(Qt)(w, () => y.$emit("close-picker"))) }, ge(y.cancelText), 545)) : Z("", true), f(r).showNow ? (T(), F("button", { key: 1, type: "button", class: "dp__action_button dp__action_cancel", onClick: V[2] || (V[2] = (w) => y.$emit("select-now")), onKeydown: V[3] || (V[3] = (w) => f(Qt)(w, () => y.$emit("select-now"))) }, ge(y.nowButtonLabel), 33)) : Z("", true), f(r).showSelect ? (T(), F("button", { key: 2, ref_key: "selectButtonRef", ref: N, type: "button", class: "dp__action_button dp__action_select", disabled: C.value, "data-test": "select-button", onKeydown: V[4] || (V[4] = (w) => f(Qt)(w, () => j())), onClick: j }, ge(y.selectText), 41, ew)) : Z("", true)], 64))], 512)], 64))], 512));
} }), nw = { class: "dp__selection_grid_header" }, aw = ["aria-selected", "aria-disabled", "data-test", "onClick", "onKeydown", "onMouseover"], rw = ["aria-label"], ul = /* @__PURE__ */ Rt({ __name: "SelectionOverlay", props: { items: {}, type: {}, isLast: { type: Boolean }, arrowNavigation: { type: Boolean }, skipButtonRef: { type: Boolean }, headerRefs: {}, hideNavigation: {}, escClose: { type: Boolean }, useRelative: { type: Boolean }, height: {}, textInput: { type: [Boolean, Object] }, config: {}, noOverlayFocus: { type: Boolean }, focusValue: {}, menuWrapRef: {}, ariaLabels: {} }, emits: ["selected", "toggle", "reset-flow", "hover-value"], setup(e, { expose: t, emit: n }) {
  const { setSelectionGrid: a, buildMultiLevelMatrix: r, setMonthPicker: l } = ka(), o = n, i = e, { defaultedAriaLabels: s, defaultedTextInput: c, defaultedConfig: d } = ct(i), { hideNavigationButtons: u } = go(), p = te(false), v = te(null), b = te(null), h = te([]), N = te(), I = te(null), x = te(0), _ = te(null);
  Xc(() => {
    v.value = null;
  }), ot(() => {
    bt().then(() => H()), i.noOverlayFocus || R(), g(true);
  }), br(() => g(false));
  const g = (q) => {
    var oe;
    i.arrowNavigation && ((oe = i.headerRefs) != null && oe.length ? l(q) : a(q));
  }, R = () => {
    var q;
    const oe = kt(b);
    oe && (c.value.enabled || (v.value ? (q = v.value) == null || q.focus({ preventScroll: true }) : oe.focus({ preventScroll: true })), p.value = oe.clientHeight < oe.scrollHeight);
  }, M = J(() => ({ dp__overlay: true, "dp--overlay-absolute": !i.useRelative, "dp--overlay-relative": i.useRelative })), C = J(() => i.useRelative ? { height: `${i.height}px`, width: "260px" } : void 0), Y = J(() => ({ dp__overlay_col: true })), P = J(() => ({ dp__btn: true, dp__button: true, dp__overlay_action: true, dp__over_action_scroll: p.value, dp__button_bottom: i.isLast })), $ = J(() => {
    var q, oe;
    return { dp__overlay_container: true, dp__container_flex: ((q = i.items) == null ? void 0 : q.length) <= 6, dp__container_block: ((oe = i.items) == null ? void 0 : oe.length) > 6 };
  });
  He(() => i.items, () => H(false), { deep: true });
  const H = (q = true) => {
    bt().then(() => {
      const oe = kt(v), S = kt(b), W = kt(I), G = kt(_), D = W ? W.getBoundingClientRect().height : 0;
      S && (S.getBoundingClientRect().height ? x.value = S.getBoundingClientRect().height - D : x.value = d.value.modeHeight - D), oe && G && q && (G.scrollTop = oe.offsetTop - G.offsetTop - (x.value / 2 - oe.getBoundingClientRect().height) - D);
    });
  }, z = (q) => {
    q.disabled || o("selected", q.value);
  }, se = () => {
    o("toggle"), o("reset-flow");
  }, j = () => {
    i.escClose && se();
  }, y = (q, oe, S, W) => {
    q && ((oe.active || oe.value === i.focusValue) && (v.value = q), i.arrowNavigation && (Array.isArray(h.value[S]) ? h.value[S][W] = q : h.value[S] = [q], V()));
  }, V = () => {
    var q, oe;
    const S = (q = i.headerRefs) != null && q.length ? [i.headerRefs].concat(h.value) : h.value.concat([i.skipButtonRef ? [] : [I.value]]);
    r(Tt(S), (oe = i.headerRefs) != null && oe.length ? "monthPicker" : "selectionGrid");
  }, w = (q) => {
    i.arrowNavigation || pa(q, d.value, true);
  }, ae = (q) => {
    N.value = q, o("hover-value", q);
  }, ce = () => {
    if (se(), !i.isLast) {
      const q = Cg(i.menuWrapRef ?? null, "action-row");
      if (q) {
        const oe = fp(q);
        oe == null || oe.focus();
      }
    }
  }, ve = (q) => {
    switch (q.key) {
      case it.esc:
        return j();
      case it.arrowLeft:
        return w(q);
      case it.arrowRight:
        return w(q);
      case it.arrowUp:
        return w(q);
      case it.arrowDown:
        return w(q);
      default:
        return;
    }
  }, _e = (q) => {
    if (q.key === it.enter) return se();
    if (q.key === it.tab) return ce();
  };
  return t({ focusGrid: R }), (q, oe) => {
    var S;
    return T(), F("div", { ref_key: "gridWrapRef", ref: b, class: pe(M.value), style: Lt(C.value), role: "dialog", tabindex: "0", onKeydown: ve, onClick: oe[0] || (oe[0] = da(() => {
    }, ["prevent"])) }, [L("div", { ref_key: "containerRef", ref: _, class: pe($.value), role: "grid", style: Lt({ "--dp-overlay-height": `${x.value}px` }) }, [L("div", nw, [ye(q.$slots, "header")]), q.$slots.overlay ? ye(q.$slots, "overlay", { key: 0 }) : (T(true), F(Ce, { key: 1 }, Ve(q.items, (W, G) => (T(), F("div", { key: G, class: pe(["dp__overlay_row", { dp__flex_row: q.items.length >= 3 }]), role: "row" }, [(T(true), F(Ce, null, Ve(W, (D, fe) => (T(), F("div", { key: D.value, ref_for: true, ref: (Ae) => y(Ae, D, G, fe), role: "gridcell", class: pe(Y.value), "aria-selected": D.active || void 0, "aria-disabled": D.disabled || void 0, tabindex: "0", "data-test": D.text, onClick: da((Ae) => z(D), ["prevent"]), onKeydown: (Ae) => f(Qt)(Ae, () => z(D), true), onMouseover: (Ae) => ae(D.value) }, [L("div", { class: pe(D.className) }, [q.$slots.item ? ye(q.$slots, "item", { key: 0, item: D }) : Z("", true), q.$slots.item ? Z("", true) : (T(), F(Ce, { key: 1 }, [Ge(ge(D.text), 1)], 64))], 2)], 42, aw))), 128))], 2))), 128))], 6), q.$slots["button-icon"] ? zn((T(), F("button", { key: 0, ref_key: "toggleButton", ref: I, type: "button", "aria-label": (S = f(s)) == null ? void 0 : S.toggleOverlay, class: pe(P.value), tabindex: "0", onClick: se, onKeydown: _e }, [ye(q.$slots, "button-icon")], 42, rw)), [[ca, !f(u)(q.hideNavigation, q.type)]]) : Z("", true)], 38);
  };
} }), mo = /* @__PURE__ */ Rt({ __name: "InstanceWrap", props: { multiCalendars: {}, stretch: { type: Boolean }, collapse: { type: Boolean } }, setup(e) {
  const t = e, n = J(() => t.multiCalendars > 0 ? [...Array(t.multiCalendars).keys()] : [0]), a = J(() => ({ dp__instance_calendar: t.multiCalendars > 0 }));
  return (r, l) => (T(), F("div", { class: pe({ dp__menu_inner: !r.stretch, "dp--menu--inner-stretched": r.stretch, dp__flex_display: r.multiCalendars > 0, "dp--flex-display-collapsed": r.collapse }) }, [(T(true), F(Ce, null, Ve(n.value, (o, i) => (T(), F("div", { key: o, class: pe(a.value) }, [ye(r.$slots, "default", { instance: o, index: i })], 2))), 128))], 2));
} }), lw = ["aria-label", "aria-disabled"], qr = /* @__PURE__ */ Rt({ compatConfig: { MODE: 3 }, __name: "ArrowBtn", props: { ariaLabel: {}, disabled: { type: Boolean } }, emits: ["activate", "set-ref"], setup(e, { emit: t }) {
  const n = t, a = te(null);
  return ot(() => n("set-ref", a)), (r, l) => (T(), F("button", { ref_key: "elRef", ref: a, type: "button", class: "dp__btn dp--arrow-btn-nav", tabindex: "0", "aria-label": r.ariaLabel, "aria-disabled": r.disabled || void 0, onClick: l[0] || (l[0] = (o) => r.$emit("activate")), onKeydown: l[1] || (l[1] = (o) => f(Qt)(o, () => r.$emit("activate"), true)) }, [L("span", { class: pe(["dp__inner_nav", { dp__inner_nav_disabled: r.disabled }]) }, [ye(r.$slots, "default")], 2)], 40, lw));
} }), ow = { class: "dp--year-mode-picker" }, iw = ["aria-label", "data-test"], Sp = /* @__PURE__ */ Rt({ __name: "YearModePicker", props: { ..._n, showYearPicker: { type: Boolean, default: false }, items: { type: Array, default: () => [] }, instance: { type: Number, default: 0 }, year: { type: Number, default: 0 }, isDisabled: { type: Function, default: () => false } }, emits: ["toggle-year-picker", "year-select", "handle-year"], setup(e, { emit: t }) {
  const n = t, a = e, { showRightIcon: r, showLeftIcon: l } = go(), { defaultedConfig: o, defaultedMultiCalendars: i, defaultedAriaLabels: s, defaultedTransitions: c, defaultedUI: d } = ct(a), { showTransition: u, transitionName: p } = cl(c), v = (N = false, I) => {
    n("toggle-year-picker", { flow: N, show: I });
  }, b = (N) => {
    n("year-select", N);
  }, h = (N = false) => {
    n("handle-year", N);
  };
  return (N, I) => {
    var x, _, g, R, M;
    return T(), F("div", ow, [f(l)(f(i), e.instance) ? (T(), Pe(qr, { key: 0, ref: "mpPrevIconRef", "aria-label": (x = f(s)) == null ? void 0 : x.prevYear, disabled: e.isDisabled(false), class: pe((_ = f(d)) == null ? void 0 : _.navBtnPrev), onActivate: I[0] || (I[0] = (C) => h(false)) }, { default: Ie(() => [N.$slots["arrow-left"] ? ye(N.$slots, "arrow-left", { key: 0 }) : Z("", true), N.$slots["arrow-left"] ? Z("", true) : (T(), Pe(f(os), { key: 1 }))]), _: 3 }, 8, ["aria-label", "disabled", "class"])) : Z("", true), L("button", { ref: "mpYearButtonRef", class: "dp__btn dp--year-select", type: "button", "aria-label": (g = f(s)) == null ? void 0 : g.openYearsOverlay, "data-test": `year-mode-btn-${e.instance}`, onClick: I[1] || (I[1] = () => v(false)), onKeydown: I[2] || (I[2] = gi(() => v(false), ["enter"])) }, [N.$slots.year ? ye(N.$slots, "year", { key: 0, year: e.year }) : Z("", true), N.$slots.year ? Z("", true) : (T(), F(Ce, { key: 1 }, [Ge(ge(e.year), 1)], 64))], 40, iw), f(r)(f(i), e.instance) ? (T(), Pe(qr, { key: 1, ref: "mpNextIconRef", "aria-label": (R = f(s)) == null ? void 0 : R.nextYear, disabled: e.isDisabled(true), class: pe((M = f(d)) == null ? void 0 : M.navBtnNext), onActivate: I[3] || (I[3] = (C) => h(true)) }, { default: Ie(() => [N.$slots["arrow-right"] ? ye(N.$slots, "arrow-right", { key: 0 }) : Z("", true), N.$slots["arrow-right"] ? Z("", true) : (T(), Pe(f(is), { key: 1 }))]), _: 3 }, 8, ["aria-label", "disabled", "class"])) : Z("", true), Ne(_r, { name: f(p)(e.showYearPicker), css: f(u) }, { default: Ie(() => [e.showYearPicker ? (T(), Pe(ul, { key: 0, items: e.items, "text-input": N.textInput, "esc-close": N.escClose, config: N.config, "is-last": N.autoApply && !f(o).keepActionRow, "hide-navigation": N.hideNavigation, "aria-labels": N.ariaLabels, type: "year", onToggle: v, onSelected: I[4] || (I[4] = (C) => b(C)) }, jt({ "button-icon": Ie(() => [N.$slots["calendar-icon"] ? ye(N.$slots, "calendar-icon", { key: 0 }) : Z("", true), N.$slots["calendar-icon"] ? Z("", true) : (T(), Pe(f(xr), { key: 1 }))]), _: 2 }, [N.$slots["year-overlay-value"] ? { name: "item", fn: Ie(({ item: C }) => [ye(N.$slots, "year-overlay-value", { text: C.text, value: C.value })]), key: "0" } : void 0]), 1032, ["items", "text-input", "esc-close", "config", "is-last", "hide-navigation", "aria-labels"])) : Z("", true)]), _: 3 }, 8, ["name", "css"])]);
  };
} }), hs = (e, t, n) => {
  if (t.value && Array.isArray(t.value)) if (t.value.some((a) => qe(e, a))) {
    const a = t.value.filter((r) => !qe(r, e));
    t.value = a.length ? a : null;
  } else (n && +n > t.value.length || !n) && t.value.push(e);
  else t.value = [e];
}, gs = (e, t, n) => {
  let a = e.value ? e.value.slice() : [];
  return a.length === 2 && a[1] !== null && (a = []), a.length ? pt(t, a[0]) ? (a.unshift(t), n("range-start", a[0]), n("range-start", a[1])) : (a[1] = t, n("range-end", t)) : (a = [t], n("range-start", t)), a;
}, ho = (e, t, n, a) => {
  e && (e[0] && e[1] && n && t("auto-apply"), e[0] && !e[1] && a && n && t("auto-apply"));
}, Cp = (e) => {
  Array.isArray(e.value) && e.value.length <= 2 && e.range ? e.modelValue.value = e.value.map((t) => Xt(de(t), e.timezone)) : Array.isArray(e.value) || (e.modelValue.value = Xt(de(e.value), e.timezone));
}, Mp = (e, t, n, a) => Array.isArray(t.value) && (t.value.length === 2 || t.value.length === 1 && a.value.partialRange) ? a.value.fixedStart && (gt(e, t.value[0]) || qe(e, t.value[0])) ? [t.value[0], e] : a.value.fixedEnd && (pt(e, t.value[1]) || qe(e, t.value[1])) ? [e, t.value[1]] : (n("invalid-fixed-range", e), t.value) : [], Tp = ({ multiCalendars: e, range: t, highlight: n, propDates: a, calendars: r, modelValue: l, props: o, filters: i, year: s, month: c, emit: d }) => {
  const u = J(() => ps(o.yearRange, o.locale, o.reverseYears)), p = te([false]), v = J(() => ($, H) => {
    const z = Je(fn(/* @__PURE__ */ new Date()), { month: c.value($), year: s.value($) }), se = H ? Ud(z) : Jr(z);
    return yp(se, a.value.maxDate, a.value.minDate, o.preventMinMaxNavigation, H);
  }), b = () => Array.isArray(l.value) && e.value.solo && l.value[1], h = () => {
    for (let $ = 0; $ < e.value.count; $++) if ($ === 0) r.value[$] = r.value[0];
    else if ($ === e.value.count - 1 && b()) r.value[$] = { month: Be(l.value[1]), year: Re(l.value[1]) };
    else {
      const H = Je(de(), r.value[$ - 1]);
      r.value[$] = { month: Be(H), year: Re(es(H, 1)) };
    }
  }, N = ($) => {
    if (!$) return h();
    const H = Je(de(), r.value[$]);
    return r.value[0].year = Re(up(H, e.value.count - 1)), h();
  }, I = ($, H) => {
    const z = ym(H, $);
    return t.value.showLastInRange && z > 1 ? H : $;
  }, x = ($) => o.focusStartDate || e.value.solo ? $[0] : $[1] ? I($[0], $[1]) : $[0], _ = () => {
    if (l.value) {
      const $ = Array.isArray(l.value) ? x(l.value) : l.value;
      r.value[0] = { month: Be($), year: Re($) };
    }
  }, g = () => {
    _(), e.value.count && h();
  };
  He(l, ($, H) => {
    o.isTextInputDate && JSON.stringify($ ?? {}) !== JSON.stringify(H ?? {}) && g();
  }), ot(() => {
    g();
  });
  const R = ($, H) => {
    r.value[H].year = $, d("update-month-year", { instance: H, year: $, month: r.value[H].month }), e.value.count && !e.value.solo && N(H);
  }, M = J(() => ($) => fr(u.value, (H) => {
    var z;
    const se = s.value($) === H.value, j = tl(H.value, vr(a.value.minDate), vr(a.value.maxDate)) || ((z = i.value.years) == null ? void 0 : z.includes(s.value($))), y = ms(n.value, H.value);
    return { active: se, disabled: j, highlighted: y };
  })), C = ($, H) => {
    R($, H), P(H);
  }, Y = ($, H = false) => {
    if (!v.value($, H)) {
      const z = H ? s.value($) + 1 : s.value($) - 1;
      R(z, $);
    }
  }, P = ($, H = false, z) => {
    H || d("reset-flow"), z !== void 0 ? p.value[$] = z : p.value[$] = !p.value[$], p.value[$] ? d("overlay-toggle", { open: true, overlay: Nt.year }) : (d("overlay-closed"), d("overlay-toggle", { open: false, overlay: Nt.year }));
  };
  return { isDisabled: v, groupedYears: M, showYearPicker: p, selectYear: R, toggleYearPicker: P, handleYearSelect: C, handleYear: Y };
}, sw = (e, t) => {
  const { defaultedMultiCalendars: n, defaultedAriaLabels: a, defaultedTransitions: r, defaultedConfig: l, defaultedRange: o, defaultedHighlight: i, propDates: s, defaultedTz: c, defaultedFilters: d, defaultedMultiDates: u } = ct(e), p = () => {
    e.isTextInputDate && g(Re(de(e.startDate)), 0);
  }, { modelValue: v, year: b, month: h, calendars: N } = dl(e, t, p), I = J(() => dp(e.formatLocale, e.locale, e.monthNameFormat)), x = te(null), { checkMinMaxRange: _ } = Sa(e), { selectYear: g, groupedYears: R, showYearPicker: M, toggleYearPicker: C, handleYearSelect: Y, handleYear: P, isDisabled: $ } = Tp({ modelValue: v, multiCalendars: n, range: o, highlight: i, calendars: N, year: b, propDates: s, month: h, filters: d, props: e, emit: t });
  ot(() => {
    e.startDate && (v.value && e.focusStartDate || !v.value) && g(Re(de(e.startDate)), 0);
  });
  const H = (S) => S ? { month: Be(S), year: Re(S) } : { month: null, year: null }, z = () => v.value ? Array.isArray(v.value) ? v.value.map((S) => H(S)) : H(v.value) : H(), se = (S, W) => {
    const G = N.value[S], D = z();
    return Array.isArray(D) ? D.some((fe) => fe.year === (G == null ? void 0 : G.year) && fe.month === W) : (G == null ? void 0 : G.year) === D.year && W === D.month;
  }, j = (S, W, G) => {
    var D, fe;
    const Ae = z();
    return Array.isArray(Ae) ? b.value(W) === ((D = Ae[G]) == null ? void 0 : D.year) && S === ((fe = Ae[G]) == null ? void 0 : fe.month) : false;
  }, y = (S, W) => {
    if (o.value.enabled) {
      const G = z();
      if (Array.isArray(v.value) && Array.isArray(G)) {
        const D = j(S, W, 0) || j(S, W, 1), fe = Fn(fn(de()), S, b.value(W));
        return fo(v.value, x.value, fe) && !D;
      }
      return false;
    }
    return false;
  }, V = J(() => (S) => fr(I.value, (W) => {
    var G;
    const D = se(S, W.value), fe = tl(W.value, mp(b.value(S), s.value.minDate), hp(b.value(S), s.value.maxDate)) || $g(s.value.disabledDates, b.value(S)).includes(W.value) || ((G = d.value.months) == null ? void 0 : G.includes(W.value)), Ae = y(W.value, S), re = _p(i.value, W.value, b.value(S));
    return { active: D, disabled: fe, isBetween: Ae, highlighted: re };
  })), w = (S, W) => Fn(fn(de()), S, b.value(W)), ae = (S, W) => {
    const G = v.value ? v.value : fn(/* @__PURE__ */ new Date());
    v.value = Fn(G, S, b.value(W)), t("auto-apply"), t("update-flow-step");
  }, ce = (S, W) => {
    const G = w(S, W);
    o.value.fixedEnd || o.value.fixedStart ? v.value = Mp(G, v, t, o) : v.value ? _(G, v.value) && (v.value = gs(v, w(S, W), t)) : v.value = [w(S, W)], bt().then(() => {
      ho(v.value, t, e.autoApply, e.modelAuto);
    });
  }, ve = (S, W) => {
    hs(w(S, W), v, u.value.limit), t("auto-apply", true);
  }, _e = (S, W) => (N.value[W].month = S, oe(W, N.value[W].year, S), u.value.enabled ? ve(S, W) : o.value.enabled ? ce(S, W) : ae(S, W)), q = (S, W) => {
    g(S, W), oe(W, S, null);
  }, oe = (S, W, G) => {
    let D = G;
    if (!D && D !== 0) {
      const fe = z();
      D = Array.isArray(fe) ? fe[S].month : fe.month;
    }
    t("update-month-year", { instance: S, year: W, month: D });
  };
  return { groupedMonths: V, groupedYears: R, year: b, isDisabled: $, defaultedMultiCalendars: n, defaultedAriaLabels: a, defaultedTransitions: r, defaultedConfig: l, showYearPicker: M, modelValue: v, presetDate: (S, W) => {
    Cp({ value: S, modelValue: v, range: o.value.enabled, timezone: W ? void 0 : c.value.timezone }), t("auto-apply");
  }, setHoverDate: (S, W) => {
    x.value = w(S, W);
  }, selectMonth: _e, selectYear: q, toggleYearPicker: C, handleYearSelect: Y, handleYear: P, getModelMonthYear: z };
}, uw = /* @__PURE__ */ Rt({ compatConfig: { MODE: 3 }, __name: "MonthPicker", props: { ..._n }, emits: ["update:internal-model-value", "overlay-closed", "reset-flow", "range-start", "range-end", "auto-apply", "update-month-year", "update-flow-step", "mount", "invalid-fixed-range", "overlay-toggle"], setup(e, { expose: t, emit: n }) {
  const a = n, r = za(), l = tn(r, "yearMode"), o = e;
  ot(() => {
    o.shadow || a("mount", null);
  });
  const { groupedMonths: i, groupedYears: s, year: c, isDisabled: d, defaultedMultiCalendars: u, defaultedConfig: p, showYearPicker: v, modelValue: b, presetDate: h, setHoverDate: N, selectMonth: I, selectYear: x, toggleYearPicker: _, handleYearSelect: g, handleYear: R, getModelMonthYear: M } = sw(o, a);
  return t({ getSidebarProps: () => ({ modelValue: b, year: c, getModelMonthYear: M, selectMonth: I, selectYear: x, handleYear: R }), presetDate: h, toggleYearPicker: (C) => _(0, C) }), (C, Y) => (T(), Pe(mo, { "multi-calendars": f(u).count, collapse: C.collapse, stretch: "" }, { default: Ie(({ instance: P }) => [C.$slots["top-extra"] ? ye(C.$slots, "top-extra", { key: 0, value: C.internalModelValue }) : Z("", true), C.$slots["month-year"] ? ye(C.$slots, "month-year", Ot(ft({ key: 1 }, { year: f(c), months: f(i)(P), years: f(s)(P), selectMonth: f(I), selectYear: f(x), instance: P }))) : (T(), Pe(ul, { key: 2, items: f(i)(P), "arrow-navigation": C.arrowNavigation, "is-last": C.autoApply && !f(p).keepActionRow, "esc-close": C.escClose, height: f(p).modeHeight, config: C.config, "no-overlay-focus": !!(C.noOverlayFocus || C.textInput), "use-relative": "", type: "month", onSelected: ($) => f(I)($, P), onHoverValue: ($) => f(N)($, P) }, jt({ header: Ie(() => [Ne(Sp, ft(C.$props, { items: f(s)(P), instance: P, "show-year-picker": f(v)[P], year: f(c)(P), "is-disabled": ($) => f(d)(P, $), onHandleYear: ($) => f(R)(P, $), onYearSelect: ($) => f(g)($, P), onToggleYearPicker: ($) => f(_)(P, $ == null ? void 0 : $.flow, $ == null ? void 0 : $.show) }), jt({ _: 2 }, [Ve(f(l), ($, H) => ({ name: $, fn: Ie((z) => [ye(C.$slots, $, Ot(Zt(z)))]) }))]), 1040, ["items", "instance", "show-year-picker", "year", "is-disabled", "onHandleYear", "onYearSelect", "onToggleYearPicker"])]), _: 2 }, [C.$slots["month-overlay-value"] ? { name: "item", fn: Ie(({ item: $ }) => [ye(C.$slots, "month-overlay-value", { text: $.text, value: $.value })]), key: "0" } : void 0]), 1032, ["items", "arrow-navigation", "is-last", "esc-close", "height", "config", "no-overlay-focus", "onSelected", "onHoverValue"]))]), _: 3 }, 8, ["multi-calendars", "collapse"]));
} }), cw = (e, t) => {
  const n = () => {
    e.isTextInputDate && (d.value = Re(de(e.startDate)));
  }, { modelValue: a } = dl(e, t, n), r = te(null), { defaultedHighlight: l, defaultedMultiDates: o, defaultedFilters: i, defaultedRange: s, propDates: c } = ct(e), d = te();
  ot(() => {
    e.startDate && (a.value && e.focusStartDate || !a.value) && (d.value = Re(de(e.startDate)));
  });
  const u = (h) => Array.isArray(a.value) ? a.value.some((N) => Re(N) === h) : a.value ? Re(a.value) === h : false, p = (h) => s.value.enabled && Array.isArray(a.value) ? fo(a.value, r.value, b(h)) : false, v = J(() => fr(ps(e.yearRange, e.locale, e.reverseYears), (h) => {
    const N = u(h.value), I = tl(h.value, vr(c.value.minDate), vr(c.value.maxDate)) || i.value.years.includes(h.value), x = p(h.value) && !N, _ = ms(l.value, h.value);
    return { active: N, disabled: I, isBetween: x, highlighted: _ };
  })), b = (h) => An(fn(Jr(/* @__PURE__ */ new Date())), h);
  return { groupedYears: v, modelValue: a, focusYear: d, setHoverValue: (h) => {
    r.value = An(fn(/* @__PURE__ */ new Date()), h);
  }, selectYear: (h) => {
    var N;
    if (t("update-month-year", { instance: 0, year: h }), o.value.enabled) return a.value ? Array.isArray(a.value) && (((N = a.value) == null ? void 0 : N.map((I) => Re(I))).includes(h) ? a.value = a.value.filter((I) => Re(I) !== h) : a.value.push(An(Wt(de()), h))) : a.value = [An(Wt(Jr(de())), h)], t("auto-apply", true);
    s.value.enabled ? (a.value = gs(a, b(h), t), bt().then(() => {
      ho(a.value, t, e.autoApply, e.modelAuto);
    })) : (a.value = b(h), t("auto-apply"));
  } };
}, dw = /* @__PURE__ */ Rt({ compatConfig: { MODE: 3 }, __name: "YearPicker", props: { ..._n }, emits: ["update:internal-model-value", "reset-flow", "range-start", "range-end", "auto-apply", "update-month-year"], setup(e, { expose: t, emit: n }) {
  const a = n, r = e, { groupedYears: l, modelValue: o, focusYear: i, selectYear: s, setHoverValue: c } = cw(r, a), { defaultedConfig: d } = ct(r);
  return t({ getSidebarProps: () => ({ modelValue: o, selectYear: s }) }), (u, p) => (T(), F("div", null, [u.$slots["top-extra"] ? ye(u.$slots, "top-extra", { key: 0, value: u.internalModelValue }) : Z("", true), u.$slots["month-year"] ? ye(u.$slots, "month-year", Ot(ft({ key: 1 }, { years: f(l), selectYear: f(s) }))) : (T(), Pe(ul, { key: 2, items: f(l), "is-last": u.autoApply && !f(d).keepActionRow, height: f(d).modeHeight, config: u.config, "no-overlay-focus": !!(u.noOverlayFocus || u.textInput), "focus-value": f(i), type: "year", "use-relative": "", onSelected: f(s), onHoverValue: f(c) }, jt({ _: 2 }, [u.$slots["year-overlay-value"] ? { name: "item", fn: Ie(({ item: v }) => [ye(u.$slots, "year-overlay-value", { text: v.text, value: v.value })]), key: "0" } : void 0]), 1032, ["items", "is-last", "height", "config", "no-overlay-focus", "focus-value", "onSelected", "onHoverValue"]))]));
} }), pw = { key: 0, class: "dp__time_input" }, fw = ["data-test", "aria-label", "onKeydown", "onClick", "onMousedown"], vw = L("span", { class: "dp__tp_inline_btn_bar dp__tp_btn_in_l" }, null, -1), mw = L("span", { class: "dp__tp_inline_btn_bar dp__tp_btn_in_r" }, null, -1), hw = ["aria-label", "disabled", "data-test", "onKeydown", "onClick"], gw = ["data-test", "aria-label", "onKeydown", "onClick", "onMousedown"], ww = L("span", { class: "dp__tp_inline_btn_bar dp__tp_btn_in_l" }, null, -1), yw = L("span", { class: "dp__tp_inline_btn_bar dp__tp_btn_in_r" }, null, -1), bw = { key: 0 }, _w = ["aria-label"], xw = /* @__PURE__ */ Rt({ compatConfig: { MODE: 3 }, __name: "TimeInput", props: { hours: { type: Number, default: 0 }, minutes: { type: Number, default: 0 }, seconds: { type: Number, default: 0 }, closeTimePickerBtn: { type: Object, default: null }, order: { type: Number, default: 0 }, disabledTimesConfig: { type: Function, default: null }, validateTime: { type: Function, default: () => false }, ..._n }, emits: ["set-hours", "set-minutes", "update:hours", "update:minutes", "update:seconds", "reset-flow", "mounted", "overlay-closed", "overlay-opened", "am-pm-change"], setup(e, { expose: t, emit: n }) {
  const a = n, r = e, { setTimePickerElements: l, setTimePickerBackRef: o } = ka(), { defaultedAriaLabels: i, defaultedTransitions: s, defaultedFilters: c, defaultedConfig: d, defaultedRange: u } = ct(r), { transitionName: p, showTransition: v } = cl(s), b = un({ hours: false, minutes: false, seconds: false }), h = te("AM"), N = te(null), I = te([]), x = te();
  ot(() => {
    a("mounted");
  });
  const _ = (O) => Je(/* @__PURE__ */ new Date(), { hours: O.hours, minutes: O.minutes, seconds: r.enableSeconds ? O.seconds : 0, milliseconds: 0 }), g = J(() => (O) => V(O, r[O]) || M(O, r[O])), R = J(() => ({ hours: r.hours, minutes: r.minutes, seconds: r.seconds })), M = (O, m) => u.value.enabled && !u.value.disableTimeRangeValidation ? !r.validateTime(O, m) : false, C = (O, m) => {
    if (u.value.enabled && !u.value.disableTimeRangeValidation) {
      const k = m ? +r[`${O}Increment`] : -+r[`${O}Increment`], E = r[O] + k;
      return !r.validateTime(O, E);
    }
    return false;
  }, Y = J(() => (O) => !_e(+r[O] + +r[`${O}Increment`], O) || C(O, true)), P = J(() => (O) => !_e(+r[O] - +r[`${O}Increment`], O) || C(O, false)), $ = (O, m) => Yd(Je(de(), O), m), H = (O, m) => vg(Je(de(), O), m), z = J(() => ({ dp__time_col: true, dp__time_col_block: !r.timePickerInline, dp__time_col_reg_block: !r.enableSeconds && r.is24 && !r.timePickerInline, dp__time_col_reg_inline: !r.enableSeconds && r.is24 && r.timePickerInline, dp__time_col_reg_with_button: !r.enableSeconds && !r.is24, dp__time_col_sec: r.enableSeconds && r.is24, dp__time_col_sec_with_button: r.enableSeconds && !r.is24 })), se = J(() => {
    const O = [{ type: "hours" }];
    return r.enableMinutes && O.push({ type: "", separator: true }, { type: "minutes" }), r.enableSeconds && O.push({ type: "", separator: true }, { type: "seconds" }), O;
  }), j = J(() => se.value.filter((O) => !O.separator)), y = J(() => (O) => {
    if (O === "hours") {
      const m = D(+r.hours);
      return { text: m < 10 ? `0${m}` : `${m}`, value: m };
    }
    return { text: r[O] < 10 ? `0${r[O]}` : `${r[O]}`, value: r[O] };
  }), V = (O, m) => {
    var k;
    if (!r.disabledTimesConfig) return false;
    const E = r.disabledTimesConfig(r.order, O === "hours" ? m : void 0);
    return E[O] ? !!((k = E[O]) != null && k.includes(m)) : true;
  }, w = (O, m) => m !== "hours" || h.value === "AM" ? O : O + 12, ae = (O) => {
    const m = r.is24 ? 24 : 12, k = O === "hours" ? m : 60, E = +r[`${O}GridIncrement`], B = O === "hours" && !r.is24 ? E : 0, X = [];
    for (let A = B; A < k; A += E) X.push({ value: r.is24 ? A : w(A, O), text: A < 10 ? `0${A}` : `${A}` });
    return O === "hours" && !r.is24 && X.unshift({ value: h.value === "PM" ? 12 : 0, text: "12" }), fr(X, (A) => ({ active: false, disabled: c.value.times[O].includes(A.value) || !_e(A.value, O) || V(O, A.value) || M(O, A.value) }));
  }, ce = (O) => O >= 0 ? O : 59, ve = (O) => O >= 0 ? O : 23, _e = (O, m) => {
    const k = r.minTime ? _(zo(r.minTime)) : null, E = r.maxTime ? _(zo(r.maxTime)) : null, B = _(zo(R.value, m, m === "minutes" || m === "seconds" ? ce(O) : ve(O)));
    return k && E ? (el(B, E) || er(B, E)) && (dr(B, k) || er(B, k)) : k ? dr(B, k) || er(B, k) : E ? el(B, E) || er(B, E) : true;
  }, q = (O) => r[`no${O[0].toUpperCase() + O.slice(1)}Overlay`], oe = (O) => {
    q(O) || (b[O] = !b[O], b[O] ? a("overlay-opened", O) : a("overlay-closed", O));
  }, S = (O) => O === "hours" ? Wn : O === "minutes" ? ha : cr, W = () => {
    x.value && clearTimeout(x.value);
  }, G = (O, m = true, k) => {
    const E = m ? $ : H, B = m ? +r[`${O}Increment`] : -+r[`${O}Increment`];
    _e(+r[O] + B, O) && a(`update:${O}`, S(O)(E({ [O]: +r[O] }, { [O]: +r[`${O}Increment`] }))), !(k != null && k.keyboard) && d.value.timeArrowHoldThreshold && (x.value = setTimeout(() => {
      G(O, m);
    }, d.value.timeArrowHoldThreshold));
  }, D = (O) => r.is24 ? O : (O >= 12 ? h.value = "PM" : h.value = "AM", bg(O)), fe = () => {
    h.value === "PM" ? (h.value = "AM", a("update:hours", r.hours - 12)) : (h.value = "PM", a("update:hours", r.hours + 12)), a("am-pm-change", h.value);
  }, Ae = (O) => {
    b[O] = true;
  }, re = (O, m, k) => {
    if (O && r.arrowNavigation) {
      Array.isArray(I.value[m]) ? I.value[m][k] = O : I.value[m] = [O];
      const E = I.value.reduce((B, X) => X.map((A, U) => [...B[U] || [], X[U]]), []);
      o(r.closeTimePickerBtn), N.value && (E[1] = E[1].concat(N.value)), l(E, r.order);
    }
  }, Oe = (O, m) => (oe(O), a(`update:${O}`, m));
  return t({ openChildCmp: Ae }), (O, m) => {
    var k;
    return O.disabled ? Z("", true) : (T(), F("div", pw, [(T(true), F(Ce, null, Ve(se.value, (E, B) => {
      var X, A, U;
      return T(), F("div", { key: B, class: pe(z.value) }, [E.separator ? (T(), F(Ce, { key: 0 }, [Ge(" : ")], 64)) : (T(), F(Ce, { key: 1 }, [L("button", { ref_for: true, ref: (ee) => re(ee, B, 0), type: "button", class: pe({ dp__btn: true, dp__inc_dec_button: !O.timePickerInline, dp__inc_dec_button_inline: O.timePickerInline, dp__tp_inline_btn_top: O.timePickerInline, dp__inc_dec_button_disabled: Y.value(E.type) }), "data-test": `${E.type}-time-inc-btn-${r.order}`, "aria-label": (X = f(i)) == null ? void 0 : X.incrementValue(E.type), tabindex: "0", onKeydown: (ee) => f(Qt)(ee, () => G(E.type, true, { keyboard: true }), true), onClick: (ee) => f(d).timeArrowHoldThreshold ? void 0 : G(E.type, true), onMousedown: (ee) => f(d).timeArrowHoldThreshold ? G(E.type, true) : void 0, onMouseup: W }, [r.timePickerInline ? (T(), F(Ce, { key: 1 }, [O.$slots["tp-inline-arrow-up"] ? ye(O.$slots, "tp-inline-arrow-up", { key: 0 }) : (T(), F(Ce, { key: 1 }, [vw, mw], 64))], 64)) : (T(), F(Ce, { key: 0 }, [O.$slots["arrow-up"] ? ye(O.$slots, "arrow-up", { key: 0 }) : Z("", true), O.$slots["arrow-up"] ? Z("", true) : (T(), Pe(f(us), { key: 1 }))], 64))], 42, fw), L("button", { ref_for: true, ref: (ee) => re(ee, B, 1), type: "button", "aria-label": (A = f(i)) == null ? void 0 : A.openTpOverlay(E.type), class: pe({ dp__time_display: true, dp__time_display_block: !O.timePickerInline, dp__time_display_inline: O.timePickerInline, "dp--time-invalid": g.value(E.type), "dp--time-overlay-btn": !g.value(E.type) }), disabled: q(E.type), tabindex: "0", "data-test": `${E.type}-toggle-overlay-btn-${r.order}`, onKeydown: (ee) => f(Qt)(ee, () => oe(E.type), true), onClick: (ee) => oe(E.type) }, [O.$slots[E.type] ? ye(O.$slots, E.type, { key: 0, text: y.value(E.type).text, value: y.value(E.type).value }) : Z("", true), O.$slots[E.type] ? Z("", true) : (T(), F(Ce, { key: 1 }, [Ge(ge(y.value(E.type).text), 1)], 64))], 42, hw), L("button", { ref_for: true, ref: (ee) => re(ee, B, 2), type: "button", class: pe({ dp__btn: true, dp__inc_dec_button: !O.timePickerInline, dp__inc_dec_button_inline: O.timePickerInline, dp__tp_inline_btn_bottom: O.timePickerInline, dp__inc_dec_button_disabled: P.value(E.type) }), "data-test": `${E.type}-time-dec-btn-${r.order}`, "aria-label": (U = f(i)) == null ? void 0 : U.decrementValue(E.type), tabindex: "0", onKeydown: (ee) => f(Qt)(ee, () => G(E.type, false, { keyboard: true }), true), onClick: (ee) => f(d).timeArrowHoldThreshold ? void 0 : G(E.type, false), onMousedown: (ee) => f(d).timeArrowHoldThreshold ? G(E.type, false) : void 0, onMouseup: W }, [r.timePickerInline ? (T(), F(Ce, { key: 1 }, [O.$slots["tp-inline-arrow-down"] ? ye(O.$slots, "tp-inline-arrow-down", { key: 0 }) : (T(), F(Ce, { key: 1 }, [ww, yw], 64))], 64)) : (T(), F(Ce, { key: 0 }, [O.$slots["arrow-down"] ? ye(O.$slots, "arrow-down", { key: 0 }) : Z("", true), O.$slots["arrow-down"] ? Z("", true) : (T(), Pe(f(cs), { key: 1 }))], 64))], 42, gw)], 64))], 2);
    }), 128)), O.is24 ? Z("", true) : (T(), F("div", bw, [O.$slots["am-pm-button"] ? ye(O.$slots, "am-pm-button", { key: 0, toggle: fe, value: h.value }) : Z("", true), O.$slots["am-pm-button"] ? Z("", true) : (T(), F("button", { key: 1, ref_key: "amPmButton", ref: N, type: "button", class: "dp__pm_am_button", role: "button", "aria-label": (k = f(i)) == null ? void 0 : k.amPmButton, tabindex: "0", onClick: fe, onKeydown: m[0] || (m[0] = (E) => f(Qt)(E, () => fe(), true)) }, ge(h.value), 41, _w))])), (T(true), F(Ce, null, Ve(j.value, (E, B) => (T(), Pe(_r, { key: B, name: f(p)(b[E.type]), css: f(v) }, { default: Ie(() => [b[E.type] ? (T(), Pe(ul, { key: 0, items: ae(E.type), "is-last": O.autoApply && !f(d).keepActionRow, "esc-close": O.escClose, type: E.type, "text-input": O.textInput, config: O.config, "arrow-navigation": O.arrowNavigation, "aria-labels": O.ariaLabels, onSelected: (X) => Oe(E.type, X), onToggle: (X) => oe(E.type), onResetFlow: m[1] || (m[1] = (X) => O.$emit("reset-flow")) }, jt({ "button-icon": Ie(() => [O.$slots["clock-icon"] ? ye(O.$slots, "clock-icon", { key: 0 }) : Z("", true), O.$slots["clock-icon"] ? Z("", true) : (T(), Pe(ol(O.timePickerInline ? f(xr) : f(ss)), { key: 1 }))]), _: 2 }, [O.$slots[`${E.type}-overlay-value`] ? { name: "item", fn: Ie(({ item: X }) => [ye(O.$slots, `${E.type}-overlay-value`, { text: X.text, value: X.value })]), key: "0" } : void 0, O.$slots[`${E.type}-overlay-header`] ? { name: "header", fn: Ie(() => [ye(O.$slots, `${E.type}-overlay-header`, { toggle: () => oe(E.type) })]), key: "1" } : void 0]), 1032, ["items", "is-last", "esc-close", "type", "text-input", "config", "arrow-navigation", "aria-labels", "onSelected", "onToggle"])) : Z("", true)]), _: 2 }, 1032, ["name", "css"]))), 128))]));
  };
} }), kw = { class: "dp--tp-wrap" }, Sw = ["aria-label", "tabindex"], Cw = ["tabindex"], Mw = ["aria-label"], Ap = /* @__PURE__ */ Rt({ compatConfig: { MODE: 3 }, __name: "TimePicker", props: { hours: { type: [Number, Array], default: 0 }, minutes: { type: [Number, Array], default: 0 }, seconds: { type: [Number, Array], default: 0 }, disabledTimesConfig: { type: Function, default: null }, validateTime: { type: Function, default: () => false }, ..._n }, emits: ["update:hours", "update:minutes", "update:seconds", "mount", "reset-flow", "overlay-opened", "overlay-closed", "am-pm-change"], setup(e, { expose: t, emit: n }) {
  const a = n, r = e, { buildMatrix: l, setTimePicker: o } = ka(), i = za(), { defaultedTransitions: s, defaultedAriaLabels: c, defaultedTextInput: d, defaultedConfig: u, defaultedRange: p } = ct(r), { transitionName: v, showTransition: b } = cl(s), { hideNavigationButtons: h } = go(), N = te(null), I = te(null), x = te([]), _ = te(null);
  ot(() => {
    a("mount"), !r.timePicker && r.arrowNavigation ? l([kt(N.value)], "time") : o(true, r.timePicker);
  });
  const g = J(() => p.value.enabled && r.modelAuto ? pp(r.internalModelValue) : true), R = te(false), M = (w) => ({ hours: Array.isArray(r.hours) ? r.hours[w] : r.hours, minutes: Array.isArray(r.minutes) ? r.minutes[w] : r.minutes, seconds: Array.isArray(r.seconds) ? r.seconds[w] : r.seconds }), C = J(() => {
    const w = [];
    if (p.value.enabled) for (let ae = 0; ae < 2; ae++) w.push(M(ae));
    else w.push(M(0));
    return w;
  }), Y = (w, ae = false, ce = "") => {
    ae || a("reset-flow"), R.value = w, a(w ? "overlay-opened" : "overlay-closed", Nt.time), r.arrowNavigation && o(w), bt(() => {
      ce !== "" && x.value[0] && x.value[0].openChildCmp(ce);
    });
  }, P = J(() => ({ dp__btn: true, dp__button: true, dp__button_bottom: r.autoApply && !u.value.keepActionRow })), $ = tn(i, "timePicker"), H = (w, ae, ce) => p.value.enabled ? ae === 0 ? [w, C.value[1][ce]] : [C.value[0][ce], w] : w, z = (w) => {
    a("update:hours", w);
  }, se = (w) => {
    a("update:minutes", w);
  }, j = (w) => {
    a("update:seconds", w);
  }, y = () => {
    if (_.value && !d.value.enabled && !r.noOverlayFocus) {
      const w = fp(_.value);
      w && w.focus({ preventScroll: true });
    }
  }, V = (w) => {
    a("overlay-closed", w);
  };
  return t({ toggleTimePicker: Y }), (w, ae) => {
    var ce;
    return T(), F("div", kw, [!w.timePicker && !w.timePickerInline ? zn((T(), F("button", { key: 0, ref_key: "openTimePickerBtn", ref: N, type: "button", class: pe(P.value), "aria-label": (ce = f(c)) == null ? void 0 : ce.openTimePicker, tabindex: w.noOverlayFocus ? void 0 : 0, "data-test": "open-time-picker-btn", onKeydown: ae[0] || (ae[0] = (ve) => f(Qt)(ve, () => Y(true))), onClick: ae[1] || (ae[1] = (ve) => Y(true)) }, [w.$slots["clock-icon"] ? ye(w.$slots, "clock-icon", { key: 0 }) : Z("", true), w.$slots["clock-icon"] ? Z("", true) : (T(), Pe(f(ss), { key: 1 }))], 42, Sw)), [[ca, !f(h)(w.hideNavigation, "time")]]) : Z("", true), Ne(_r, { name: f(v)(R.value), css: f(b) && !w.timePickerInline }, { default: Ie(() => {
      var ve;
      return [R.value || w.timePicker || w.timePickerInline ? (T(), F("div", { key: 0, ref_key: "overlayRef", ref: _, class: pe({ dp__overlay: !w.timePickerInline, "dp--overlay-absolute": !r.timePicker && !w.timePickerInline, "dp--overlay-relative": r.timePicker }), style: Lt(w.timePicker ? { height: `${f(u).modeHeight}px` } : void 0), tabindex: w.timePickerInline ? void 0 : 0 }, [L("div", { class: pe(w.timePickerInline ? "dp__time_picker_inline_container" : "dp__overlay_container dp__container_flex dp__time_picker_overlay_container"), style: { display: "flex" } }, [w.$slots["time-picker-overlay"] ? ye(w.$slots, "time-picker-overlay", { key: 0, hours: e.hours, minutes: e.minutes, seconds: e.seconds, setHours: z, setMinutes: se, setSeconds: j }) : Z("", true), w.$slots["time-picker-overlay"] ? Z("", true) : (T(), F("div", { key: 1, class: pe(w.timePickerInline ? "dp__flex" : "dp__overlay_row dp__flex_row") }, [(T(true), F(Ce, null, Ve(C.value, (_e, q) => zn((T(), Pe(xw, ft({ key: q, ref_for: true }, { ...w.$props, order: q, hours: _e.hours, minutes: _e.minutes, seconds: _e.seconds, closeTimePickerBtn: I.value, disabledTimesConfig: e.disabledTimesConfig, disabled: q === 0 ? w.fixedStart : w.fixedEnd }, { ref_for: true, ref_key: "timeInputRefs", ref: x, "validate-time": (oe, S) => e.validateTime(oe, H(S, q, oe)), "onUpdate:hours": (oe) => z(H(oe, q, "hours")), "onUpdate:minutes": (oe) => se(H(oe, q, "minutes")), "onUpdate:seconds": (oe) => j(H(oe, q, "seconds")), onMounted: y, onOverlayClosed: V, onOverlayOpened: ae[2] || (ae[2] = (oe) => w.$emit("overlay-opened", oe)), onAmPmChange: ae[3] || (ae[3] = (oe) => w.$emit("am-pm-change", oe)) }), jt({ _: 2 }, [Ve(f($), (oe, S) => ({ name: oe, fn: Ie((W) => [ye(w.$slots, oe, ft({ ref_for: true }, W))]) }))]), 1040, ["validate-time", "onUpdate:hours", "onUpdate:minutes", "onUpdate:seconds"])), [[ca, q === 0 ? true : g.value]])), 128))], 2)), !w.timePicker && !w.timePickerInline ? zn((T(), F("button", { key: 2, ref_key: "closeTimePickerBtn", ref: I, type: "button", class: pe(P.value), "aria-label": (ve = f(c)) == null ? void 0 : ve.closeTimePicker, tabindex: "0", onKeydown: ae[4] || (ae[4] = (_e) => f(Qt)(_e, () => Y(false))), onClick: ae[5] || (ae[5] = (_e) => Y(false)) }, [w.$slots["calendar-icon"] ? ye(w.$slots, "calendar-icon", { key: 0 }) : Z("", true), w.$slots["calendar-icon"] ? Z("", true) : (T(), Pe(f(xr), { key: 1 }))], 42, Mw)), [[ca, !f(h)(w.hideNavigation, "time")]]) : Z("", true)], 2)], 14, Cw)) : Z("", true)];
    }), _: 3 }, 8, ["name", "css"])]);
  };
} }), Dp = (e, t, n, a) => {
  const { defaultedRange: r } = ct(e), l = (_, g) => Array.isArray(t[_]) ? t[_][g] : t[_], o = (_) => e.enableSeconds ? Array.isArray(t.seconds) ? t.seconds[_] : t.seconds : 0, i = (_, g) => _ ? g !== void 0 ? fa(_, l("hours", g), l("minutes", g), o(g)) : fa(_, t.hours, t.minutes, o()) : sp(de(), o(g)), s = (_, g) => {
    t[_] = g;
  }, c = J(() => e.modelAuto && r.value.enabled ? Array.isArray(n.value) ? n.value.length > 1 : false : r.value.enabled), d = (_, g) => {
    const R = Object.fromEntries(Object.keys(t).map((M) => M === _ ? [M, g] : [M, t[M]].slice()));
    if (c.value && !r.value.disableTimeRangeValidation) {
      const M = (Y) => n.value ? fa(n.value[Y], R.hours[Y], R.minutes[Y], R.seconds[Y]) : null, C = (Y) => ip(n.value[Y], 0);
      return !(qe(M(0), M(1)) && (dr(M(0), C(1)) || el(M(1), C(0))));
    }
    return true;
  }, u = (_, g) => {
    d(_, g) && (s(_, g), a && a());
  }, p = (_) => {
    u("hours", _);
  }, v = (_) => {
    u("minutes", _);
  }, b = (_) => {
    u("seconds", _);
  }, h = (_, g, R, M) => {
    g && p(_), !g && !R && v(_), R && b(_), n.value && M(n.value);
  }, N = (_) => {
    if (_) {
      const g = Array.isArray(_), R = g ? [+_[0].hours, +_[1].hours] : +_.hours, M = g ? [+_[0].minutes, +_[1].minutes] : +_.minutes, C = g ? [+_[0].seconds, +_[1].seconds] : +_.seconds;
      s("hours", R), s("minutes", M), e.enableSeconds && s("seconds", C);
    }
  }, I = (_, g) => {
    const R = { hours: Array.isArray(t.hours) ? t.hours[_] : t.hours, disabledArr: [] };
    return (g || g === 0) && (R.hours = g), Array.isArray(e.disabledTimes) && (R.disabledArr = r.value.enabled && Array.isArray(e.disabledTimes[_]) ? e.disabledTimes[_] : e.disabledTimes), R;
  }, x = J(() => (_, g) => {
    var R;
    if (Array.isArray(e.disabledTimes)) {
      const { disabledArr: M, hours: C } = I(_, g), Y = M.filter((P) => +P.hours === C);
      return ((R = Y[0]) == null ? void 0 : R.minutes) === "*" ? { hours: [C], minutes: void 0, seconds: void 0 } : { hours: [], minutes: (Y == null ? void 0 : Y.map((P) => +P.minutes)) ?? [], seconds: (Y == null ? void 0 : Y.map((P) => P.seconds ? +P.seconds : void 0)) ?? [] };
    }
    return { hours: [], minutes: [], seconds: [] };
  });
  return { setTime: s, updateHours: p, updateMinutes: v, updateSeconds: b, getSetDateTime: i, updateTimeValues: h, getSecondsValue: o, assignStartTime: N, validateTime: d, disabledTimesConfig: x };
}, Tw = (e, t) => {
  const n = () => {
    e.isTextInputDate && g();
  }, { modelValue: a, time: r } = dl(e, t, n), { defaultedStartTime: l, defaultedRange: o, defaultedTz: i } = ct(e), { updateTimeValues: s, getSetDateTime: c, setTime: d, assignStartTime: u, disabledTimesConfig: p, validateTime: v } = Dp(e, r, a, b);
  function b() {
    t("update-flow-step");
  }
  const h = (M) => {
    const { hours: C, minutes: Y, seconds: P } = M;
    return { hours: +C, minutes: +Y, seconds: P ? +P : 0 };
  }, N = () => {
    if (e.startTime) {
      if (Array.isArray(e.startTime)) {
        const C = h(e.startTime[0]), Y = h(e.startTime[1]);
        return [Je(de(), C), Je(de(), Y)];
      }
      const M = h(e.startTime);
      return Je(de(), M);
    }
    return o.value.enabled ? [null, null] : null;
  }, I = () => {
    if (o.value.enabled) {
      const [M, C] = N();
      a.value = [Xt(c(M, 0), i.value.timezone), Xt(c(C, 1), i.value.timezone)];
    } else a.value = Xt(c(N()), i.value.timezone);
  }, x = (M) => Array.isArray(M) ? [Va(de(M[0])), Va(de(M[1]))] : [Va(M ?? de())], _ = (M, C, Y) => {
    d("hours", M), d("minutes", C), d("seconds", e.enableSeconds ? Y : 0);
  }, g = () => {
    const [M, C] = x(a.value);
    return o.value.enabled ? _([M.hours, C.hours], [M.minutes, C.minutes], [M.seconds, C.seconds]) : _(M.hours, M.minutes, M.seconds);
  };
  ot(() => {
    if (!e.shadow) return u(l.value), a.value ? g() : I();
  });
  const R = () => {
    Array.isArray(a.value) ? a.value = a.value.map((M, C) => M && c(M, C)) : a.value = c(a.value), t("time-update");
  };
  return { modelValue: a, time: r, disabledTimesConfig: p, updateTime: (M, C = true, Y = false) => {
    s(M, C, Y, R);
  }, validateTime: v };
}, Aw = /* @__PURE__ */ Rt({ compatConfig: { MODE: 3 }, __name: "TimePickerSolo", props: { ..._n }, emits: ["update:internal-model-value", "time-update", "am-pm-change", "mount", "reset-flow", "update-flow-step", "overlay-toggle"], setup(e, { expose: t, emit: n }) {
  const a = n, r = e, l = za(), o = tn(l, "timePicker"), i = te(null), { time: s, modelValue: c, disabledTimesConfig: d, updateTime: u, validateTime: p } = Tw(r, a);
  return ot(() => {
    r.shadow || a("mount", null);
  }), t({ getSidebarProps: () => ({ modelValue: c, time: s, updateTime: u }), toggleTimePicker: (v, b = false, h = "") => {
    var N;
    (N = i.value) == null || N.toggleTimePicker(v, b, h);
  } }), (v, b) => (T(), Pe(mo, { "multi-calendars": 0, stretch: "" }, { default: Ie(() => [Ne(Ap, ft({ ref_key: "tpRef", ref: i }, v.$props, { hours: f(s).hours, minutes: f(s).minutes, seconds: f(s).seconds, "internal-model-value": v.internalModelValue, "disabled-times-config": f(d), "validate-time": f(p), "onUpdate:hours": b[0] || (b[0] = (h) => f(u)(h)), "onUpdate:minutes": b[1] || (b[1] = (h) => f(u)(h, false)), "onUpdate:seconds": b[2] || (b[2] = (h) => f(u)(h, false, true)), onAmPmChange: b[3] || (b[3] = (h) => v.$emit("am-pm-change", h)), onResetFlow: b[4] || (b[4] = (h) => v.$emit("reset-flow")), onOverlayClosed: b[5] || (b[5] = (h) => v.$emit("overlay-toggle", { open: false, overlay: h })), onOverlayOpened: b[6] || (b[6] = (h) => v.$emit("overlay-toggle", { open: true, overlay: h })) }), jt({ _: 2 }, [Ve(f(o), (h, N) => ({ name: h, fn: Ie((I) => [ye(v.$slots, h, Ot(Zt(I)))]) }))]), 1040, ["hours", "minutes", "seconds", "internal-model-value", "disabled-times-config", "validate-time"])]), _: 3 }));
} }), Dw = { class: "dp--header-wrap" }, Lw = { key: 0, class: "dp__month_year_wrap" }, Ow = { key: 0 }, Pw = { class: "dp__month_year_wrap" }, $w = ["aria-label", "data-test", "onClick", "onKeydown"], Rw = /* @__PURE__ */ Rt({ compatConfig: { MODE: 3 }, __name: "DpHeader", props: { month: { type: Number, default: 0 }, year: { type: Number, default: 0 }, instance: { type: Number, default: 0 }, years: { type: Array, default: () => [] }, months: { type: Array, default: () => [] }, ..._n }, emits: ["update-month-year", "mount", "reset-flow", "overlay-closed", "overlay-opened"], setup(e, { expose: t, emit: n }) {
  const a = n, r = e, { defaultedTransitions: l, defaultedAriaLabels: o, defaultedMultiCalendars: i, defaultedFilters: s, defaultedConfig: c, defaultedHighlight: d, propDates: u, defaultedUI: p } = ct(r), { transitionName: v, showTransition: b } = cl(l), { buildMatrix: h } = ka(), { handleMonthYearChange: N, isDisabled: I, updateMonthYear: x } = Xg(r, a), { showLeftIcon: _, showRightIcon: g } = go(), R = te(false), M = te(false), C = te([null, null, null, null]);
  ot(() => {
    a("mount");
  });
  const Y = (q) => ({ get: () => r[q], set: (oe) => {
    const S = q === dn.month ? dn.year : dn.month;
    a("update-month-year", { [q]: oe, [S]: r[S] }), q === dn.month ? V(true) : w(true);
  } }), P = J(Y(dn.month)), $ = J(Y(dn.year)), H = J(() => (q) => ({ month: r.month, year: r.year, items: q === dn.month ? r.months : r.years, instance: r.instance, updateMonthYear: x, toggle: q === dn.month ? V : w })), z = J(() => r.months.find((q) => q.value === r.month) || { text: "", value: 0 }), se = J(() => fr(r.months, (q) => {
    const oe = r.month === q.value, S = tl(q.value, mp(r.year, u.value.minDate), hp(r.year, u.value.maxDate)) || s.value.months.includes(q.value), W = _p(d.value, q.value, r.year);
    return { active: oe, disabled: S, highlighted: W };
  })), j = J(() => fr(r.years, (q) => {
    const oe = r.year === q.value, S = tl(q.value, vr(u.value.minDate), vr(u.value.maxDate)) || s.value.years.includes(q.value), W = ms(d.value, q.value);
    return { active: oe, disabled: S, highlighted: W };
  })), y = (q, oe, S) => {
    S !== void 0 ? q.value = S : q.value = !q.value, q.value ? a("overlay-opened", oe) : a("overlay-closed", oe);
  }, V = (q = false, oe) => {
    ae(q), y(R, Nt.month, oe);
  }, w = (q = false, oe) => {
    ae(q), y(M, Nt.year, oe);
  }, ae = (q) => {
    q || a("reset-flow");
  }, ce = (q, oe) => {
    r.arrowNavigation && (C.value[oe] = kt(q), h(C.value, "monthYear"));
  }, ve = J(() => {
    var q, oe;
    return [{ type: dn.month, index: 1, toggle: V, modelValue: P.value, updateModelValue: (S) => P.value = S, text: z.value.text, showSelectionGrid: R.value, items: se.value, ariaLabel: (q = o.value) == null ? void 0 : q.openMonthsOverlay }, { type: dn.year, index: 2, toggle: w, modelValue: $.value, updateModelValue: (S) => $.value = S, text: vp(r.year, r.locale), showSelectionGrid: M.value, items: j.value, ariaLabel: (oe = o.value) == null ? void 0 : oe.openYearsOverlay }];
  }), _e = J(() => r.disableYearSelect ? [ve.value[0]] : r.yearFirst ? [...ve.value].reverse() : ve.value);
  return t({ toggleMonthPicker: V, toggleYearPicker: w, handleMonthYearChange: N }), (q, oe) => {
    var S, W, G, D, fe, Ae;
    return T(), F("div", Dw, [q.$slots["month-year"] ? (T(), F("div", Lw, [ye(q.$slots, "month-year", Ot(Zt({ month: e.month, year: e.year, months: e.months, years: e.years, updateMonthYear: f(x), handleMonthYearChange: f(N), instance: e.instance })))])) : (T(), F(Ce, { key: 1 }, [q.$slots["top-extra"] ? (T(), F("div", Ow, [ye(q.$slots, "top-extra", { value: q.internalModelValue })])) : Z("", true), L("div", Pw, [f(_)(f(i), e.instance) && !q.vertical ? (T(), Pe(qr, { key: 0, "aria-label": (S = f(o)) == null ? void 0 : S.prevMonth, disabled: f(I)(false), class: pe((W = f(p)) == null ? void 0 : W.navBtnPrev), onActivate: oe[0] || (oe[0] = (re) => f(N)(false, true)), onSetRef: oe[1] || (oe[1] = (re) => ce(re, 0)) }, { default: Ie(() => [q.$slots["arrow-left"] ? ye(q.$slots, "arrow-left", { key: 0 }) : Z("", true), q.$slots["arrow-left"] ? Z("", true) : (T(), Pe(f(os), { key: 1 }))]), _: 3 }, 8, ["aria-label", "disabled", "class"])) : Z("", true), L("div", { class: pe(["dp__month_year_wrap", { dp__year_disable_select: q.disableYearSelect }]) }, [(T(true), F(Ce, null, Ve(_e.value, (re, Oe) => (T(), F(Ce, { key: re.type }, [L("button", { ref_for: true, ref: (O) => ce(O, Oe + 1), type: "button", class: "dp__btn dp__month_year_select", tabindex: "0", "aria-label": re.ariaLabel, "data-test": `${re.type}-toggle-overlay-${e.instance}`, onClick: re.toggle, onKeydown: (O) => f(Qt)(O, () => re.toggle(), true) }, [q.$slots[re.type] ? ye(q.$slots, re.type, { key: 0, text: re.text, value: r[re.type] }) : Z("", true), q.$slots[re.type] ? Z("", true) : (T(), F(Ce, { key: 1 }, [Ge(ge(re.text), 1)], 64))], 40, $w), Ne(_r, { name: f(v)(re.showSelectionGrid), css: f(b) }, { default: Ie(() => [re.showSelectionGrid ? (T(), Pe(ul, { key: 0, items: re.items, "arrow-navigation": q.arrowNavigation, "hide-navigation": q.hideNavigation, "is-last": q.autoApply && !f(c).keepActionRow, "skip-button-ref": false, config: q.config, type: re.type, "header-refs": [], "esc-close": q.escClose, "menu-wrap-ref": q.menuWrapRef, "text-input": q.textInput, "aria-labels": q.ariaLabels, onSelected: re.updateModelValue, onToggle: re.toggle }, jt({ "button-icon": Ie(() => [q.$slots["calendar-icon"] ? ye(q.$slots, "calendar-icon", { key: 0 }) : Z("", true), q.$slots["calendar-icon"] ? Z("", true) : (T(), Pe(f(xr), { key: 1 }))]), _: 2 }, [q.$slots[`${re.type}-overlay-value`] ? { name: "item", fn: Ie(({ item: O }) => [ye(q.$slots, `${re.type}-overlay-value`, { text: O.text, value: O.value })]), key: "0" } : void 0, q.$slots[`${re.type}-overlay`] ? { name: "overlay", fn: Ie(() => [ye(q.$slots, `${re.type}-overlay`, ft({ ref_for: true }, H.value(re.type)))]), key: "1" } : void 0, q.$slots[`${re.type}-overlay-header`] ? { name: "header", fn: Ie(() => [ye(q.$slots, `${re.type}-overlay-header`, { toggle: re.toggle })]), key: "2" } : void 0]), 1032, ["items", "arrow-navigation", "hide-navigation", "is-last", "config", "type", "esc-close", "menu-wrap-ref", "text-input", "aria-labels", "onSelected", "onToggle"])) : Z("", true)]), _: 2 }, 1032, ["name", "css"])], 64))), 128))], 2), f(_)(f(i), e.instance) && q.vertical ? (T(), Pe(qr, { key: 1, "aria-label": (G = f(o)) == null ? void 0 : G.prevMonth, disabled: f(I)(false), class: pe((D = f(p)) == null ? void 0 : D.navBtnPrev), onActivate: oe[2] || (oe[2] = (re) => f(N)(false, true)) }, { default: Ie(() => [q.$slots["arrow-up"] ? ye(q.$slots, "arrow-up", { key: 0 }) : Z("", true), q.$slots["arrow-up"] ? Z("", true) : (T(), Pe(f(us), { key: 1 }))]), _: 3 }, 8, ["aria-label", "disabled", "class"])) : Z("", true), f(g)(f(i), e.instance) ? (T(), Pe(qr, { key: 2, ref: "rightIcon", disabled: f(I)(true), "aria-label": (fe = f(o)) == null ? void 0 : fe.nextMonth, class: pe((Ae = f(p)) == null ? void 0 : Ae.navBtnNext), onActivate: oe[3] || (oe[3] = (re) => f(N)(true, true)), onSetRef: oe[4] || (oe[4] = (re) => ce(re, q.disableYearSelect ? 2 : 3)) }, { default: Ie(() => [q.$slots[q.vertical ? "arrow-down" : "arrow-right"] ? ye(q.$slots, q.vertical ? "arrow-down" : "arrow-right", { key: 0 }) : Z("", true), q.$slots[q.vertical ? "arrow-down" : "arrow-right"] ? Z("", true) : (T(), Pe(ol(q.vertical ? f(cs) : f(is)), { key: 1 }))]), _: 3 }, 8, ["disabled", "aria-label", "class"])) : Z("", true)])], 64))]);
  };
} }), Ew = ["aria-label"], Nw = { class: "dp__calendar_header", role: "row" }, Iw = { key: 0, class: "dp__calendar_header_item", role: "gridcell" }, Vw = ["aria-label"], jw = L("div", { class: "dp__calendar_header_separator" }, null, -1), Bw = ["aria-label"], Fw = { key: 0, role: "gridcell", class: "dp__calendar_item dp__week_num" }, Yw = { class: "dp__cell_inner" }, qw = ["id", "aria-selected", "aria-disabled", "aria-label", "data-test", "onClick", "onKeydown", "onMouseenter", "onMouseleave", "onMousedown"], zw = /* @__PURE__ */ Rt({ compatConfig: { MODE: 3 }, __name: "DpCalendar", props: { mappedDates: { type: Array, default: () => [] }, instance: { type: Number, default: 0 }, month: { type: Number, default: 0 }, year: { type: Number, default: 0 }, ..._n }, emits: ["select-date", "set-hover-date", "handle-scroll", "mount", "handle-swipe", "handle-space", "tooltip-open", "tooltip-close"], setup(e, { expose: t, emit: n }) {
  const a = n, r = e, { buildMultiLevelMatrix: l } = ka(), { defaultedTransitions: o, defaultedConfig: i, defaultedAriaLabels: s, defaultedMultiCalendars: c, defaultedWeekNumbers: d, defaultedMultiDates: u, defaultedUI: p } = ct(r), v = te(null), b = te({ bottom: "", left: "", transform: "" }), h = te([]), N = te(null), I = te(true), x = te(""), _ = te({ startX: 0, endX: 0, startY: 0, endY: 0 }), g = te([]), R = te({ left: "50%" }), M = te(false), C = J(() => r.calendar ? r.calendar(r.mappedDates) : r.mappedDates), Y = J(() => r.dayNames ? Array.isArray(r.dayNames) ? r.dayNames : r.dayNames(r.locale, +r.weekStart) : yg(r.formatLocale, r.locale, +r.weekStart));
  ot(() => {
    a("mount", { cmp: "calendar", refs: h }), i.value.noSwipe || N.value && (N.value.addEventListener("touchstart", ce, { passive: false }), N.value.addEventListener("touchend", ve, { passive: false }), N.value.addEventListener("touchmove", _e, { passive: false })), r.monthChangeOnScroll && N.value && N.value.addEventListener("wheel", S, { passive: false });
  });
  const P = (re) => re ? r.vertical ? "vNext" : "next" : r.vertical ? "vPrevious" : "previous", $ = (re, Oe) => {
    if (r.transitions) {
      const O = Wt(Fn(de(), r.month, r.year));
      x.value = gt(Wt(Fn(de(), re, Oe)), O) ? o.value[P(true)] : o.value[P(false)], I.value = false, bt(() => {
        I.value = true;
      });
    }
  }, H = J(() => ({ [r.calendarClassName]: !!r.calendarClassName, ...p.value.calendar ?? {} })), z = J(() => (re) => {
    const Oe = _g(re);
    return { dp__marker_dot: Oe.type === "dot", dp__marker_line: Oe.type === "line" };
  }), se = J(() => (re) => qe(re, v.value)), j = J(() => ({ dp__calendar: true, dp__calendar_next: c.value.count > 0 && r.instance !== 0 })), y = J(() => (re) => r.hideOffsetDates ? re.current : true), V = async (re, Oe, O) => {
    const m = kt(h.value[Oe][O]);
    if (m) {
      const { width: k, height: E } = m.getBoundingClientRect();
      v.value = re.value;
      let B = { left: `${k / 2}px` }, X = -50;
      if (await bt(), g.value[0]) {
        const { left: A, width: U } = g.value[0].getBoundingClientRect();
        A < 0 && (B = { left: "0" }, X = 0, R.value.left = `${k / 2}px`), window.innerWidth < A + U && (B = { right: "0" }, X = 0, R.value.left = `${U - k / 2}px`);
      }
      b.value = { bottom: `${E}px`, ...B, transform: `translateX(${X}%)` }, a("tooltip-open", re.marker);
    }
  }, w = async (re, Oe, O) => {
    var m, k;
    if (M.value && u.value.enabled && u.value.dragSelect) return a("select-date", re);
    a("set-hover-date", re), (k = (m = re.marker) == null ? void 0 : m.tooltip) != null && k.length && await V(re, Oe, O);
  }, ae = (re) => {
    v.value && (v.value = null, b.value = JSON.parse(JSON.stringify({ bottom: "", left: "", transform: "" })), a("tooltip-close", re.marker));
  }, ce = (re) => {
    _.value.startX = re.changedTouches[0].screenX, _.value.startY = re.changedTouches[0].screenY;
  }, ve = (re) => {
    _.value.endX = re.changedTouches[0].screenX, _.value.endY = re.changedTouches[0].screenY, q();
  }, _e = (re) => {
    r.vertical && !r.inline && re.preventDefault();
  }, q = () => {
    const re = r.vertical ? "Y" : "X";
    Math.abs(_.value[`start${re}`] - _.value[`end${re}`]) > 10 && a("handle-swipe", _.value[`start${re}`] > _.value[`end${re}`] ? "right" : "left");
  }, oe = (re, Oe, O) => {
    re && (Array.isArray(h.value[Oe]) ? h.value[Oe][O] = re : h.value[Oe] = [re]), r.arrowNavigation && l(h.value, "calendar");
  }, S = (re) => {
    r.monthChangeOnScroll && (re.preventDefault(), a("handle-scroll", re));
  }, W = (re) => d.value.type === "local" ? as(re.value, { weekStartsOn: +r.weekStart }) : d.value.type === "iso" ? ts(re.value) : typeof d.value.type == "function" ? d.value.type(re.value) : "", G = (re) => {
    const Oe = re[0];
    return d.value.hideOnOffsetDates ? re.some((O) => O.current) ? W(Oe) : "" : W(Oe);
  }, D = (re, Oe) => {
    u.value.enabled || (pa(re, i.value), a("select-date", Oe));
  }, fe = (re) => {
    pa(re, i.value);
  }, Ae = (re) => {
    u.value.enabled && u.value.dragSelect ? (M.value = true, a("select-date", re)) : u.value.enabled && a("select-date", re);
  };
  return t({ triggerTransition: $ }), (re, Oe) => {
    var O;
    return T(), F("div", { class: pe(j.value) }, [L("div", { ref_key: "calendarWrapRef", ref: N, role: "grid", class: pe(H.value), "aria-label": (O = f(s)) == null ? void 0 : O.calendarWrap }, [L("div", Nw, [re.weekNumbers ? (T(), F("div", Iw, ge(re.weekNumName), 1)) : Z("", true), (T(true), F(Ce, null, Ve(Y.value, (m, k) => {
      var E, B;
      return T(), F("div", { key: k, class: "dp__calendar_header_item", role: "gridcell", "data-test": "calendar-header", "aria-label": (B = (E = f(s)) == null ? void 0 : E.weekDay) == null ? void 0 : B.call(E, k) }, [re.$slots["calendar-header"] ? ye(re.$slots, "calendar-header", { key: 0, day: m, index: k }) : Z("", true), re.$slots["calendar-header"] ? Z("", true) : (T(), F(Ce, { key: 1 }, [Ge(ge(m), 1)], 64))], 8, Vw);
    }), 128))]), jw, Ne(_r, { name: x.value, css: !!re.transitions }, { default: Ie(() => {
      var m;
      return [I.value ? (T(), F("div", { key: 0, class: "dp__calendar", role: "rowgroup", "aria-label": ((m = f(s)) == null ? void 0 : m.calendarDays) || void 0, onMouseleave: Oe[1] || (Oe[1] = (k) => M.value = false) }, [(T(true), F(Ce, null, Ve(C.value, (k, E) => (T(), F("div", { key: E, class: "dp__calendar_row", role: "row" }, [re.weekNumbers ? (T(), F("div", Fw, [L("div", Yw, ge(G(k.days)), 1)])) : Z("", true), (T(true), F(Ce, null, Ve(k.days, (B, X) => {
        var A, U, ee;
        return T(), F("div", { id: f(xp)(B.value), ref_for: true, ref: (le) => oe(le, E, X), key: X + E, role: "gridcell", class: "dp__calendar_item", "aria-selected": (B.classData.dp__active_date || B.classData.dp__range_start || B.classData.dp__range_start) ?? void 0, "aria-disabled": B.classData.dp__cell_disabled || void 0, "aria-label": (U = (A = f(s)) == null ? void 0 : A.day) == null ? void 0 : U.call(A, B), tabindex: "0", "data-test": B.value, onClick: da((le) => D(le, B), ["prevent"]), onKeydown: (le) => f(Qt)(le, () => re.$emit("select-date", B)), onMouseenter: (le) => w(B, E, X), onMouseleave: (le) => ae(B), onMousedown: (le) => Ae(B), onMouseup: Oe[0] || (Oe[0] = (le) => M.value = false) }, [L("div", { class: pe(["dp__cell_inner", B.classData]) }, [re.$slots.day && y.value(B) ? ye(re.$slots, "day", { key: 0, day: +B.text, date: B.value }) : Z("", true), re.$slots.day ? Z("", true) : (T(), F(Ce, { key: 1 }, [Ge(ge(B.text), 1)], 64)), B.marker && y.value(B) ? (T(), F(Ce, { key: 2 }, [re.$slots.marker ? ye(re.$slots, "marker", { key: 0, marker: B.marker, day: +B.text, date: B.value }) : (T(), F("div", { key: 1, class: pe(z.value(B.marker)), style: Lt(B.marker.color ? { backgroundColor: B.marker.color } : {}) }, null, 6))], 64)) : Z("", true), se.value(B.value) ? (T(), F("div", { key: 3, ref_for: true, ref_key: "activeTooltip", ref: g, class: "dp__marker_tooltip", style: Lt(b.value) }, [(ee = B.marker) != null && ee.tooltip ? (T(), F("div", { key: 0, class: "dp__tooltip_content", onClick: fe }, [(T(true), F(Ce, null, Ve(B.marker.tooltip, (le, ue) => (T(), F("div", { key: ue, class: "dp__tooltip_text" }, [re.$slots["marker-tooltip"] ? ye(re.$slots, "marker-tooltip", { key: 0, tooltip: le, day: B.value }) : Z("", true), re.$slots["marker-tooltip"] ? Z("", true) : (T(), F(Ce, { key: 1 }, [L("div", { class: "dp__tooltip_mark", style: Lt(le.color ? { backgroundColor: le.color } : {}) }, null, 4), L("div", null, ge(le.text), 1)], 64))]))), 128)), L("div", { class: "dp__arrow_bottom_tp", style: Lt(R.value) }, null, 4)])) : Z("", true)], 4)) : Z("", true)], 2)], 40, qw);
      }), 128))]))), 128))], 40, Bw)) : Z("", true)];
    }), _: 3 }, 8, ["name", "css"])], 10, Ew)], 2);
  };
} }), Wu = (e) => Array.isArray(e), Hw = (e, t, n, a) => {
  const r = te([]), l = te(/* @__PURE__ */ new Date()), o = te(), i = () => ve(e.isTextInputDate), { modelValue: s, calendars: c, time: d, today: u } = dl(e, t, i), { defaultedMultiCalendars: p, defaultedStartTime: v, defaultedRange: b, defaultedConfig: h, defaultedTz: N, propDates: I, defaultedMultiDates: x } = ct(e), { validateMonthYearInRange: _, isDisabled: g, isDateRangeAllowed: R, checkMinMaxRange: M } = Sa(e), { updateTimeValues: C, getSetDateTime: Y, setTime: P, assignStartTime: $, validateTime: H, disabledTimesConfig: z } = Dp(e, d, s, a), se = J(() => (Q) => c.value[Q] ? c.value[Q].month : 0), j = J(() => (Q) => c.value[Q] ? c.value[Q].year : 0), y = (Q) => !h.value.keepViewOnOffsetClick || Q ? true : !o.value, V = (Q, he, ne, me = false) => {
    var Se, Mt;
    y(me) && (c.value[Q] || (c.value[Q] = { month: 0, year: 0 }), c.value[Q].month = qu(he) ? (Se = c.value[Q]) == null ? void 0 : Se.month : he, c.value[Q].year = qu(ne) ? (Mt = c.value[Q]) == null ? void 0 : Mt.year : ne);
  }, w = () => {
    e.autoApply && t("select-date");
  };
  ot(() => {
    e.shadow || (s.value || (Oe(), v.value && $(v.value)), ve(true), e.focusStartDate && e.startDate && Oe());
  });
  const ae = J(() => {
    var Q;
    return (Q = e.flow) != null && Q.length && !e.partialFlow ? e.flowStep === e.flow.length : true;
  }), ce = () => {
    e.autoApply && ae.value && t("auto-apply");
  }, ve = (Q = false) => {
    if (s.value) return Array.isArray(s.value) ? (r.value = s.value, D(Q)) : oe(s.value, Q);
    if (p.value.count && Q && !e.startDate) return q(de(), Q);
  }, _e = () => Array.isArray(s.value) && b.value.enabled ? Be(s.value[0]) === Be(s.value[1] ?? s.value[0]) : false, q = (Q = /* @__PURE__ */ new Date(), he = false) => {
    if ((!p.value.count || !p.value.static || he) && V(0, Be(Q), Re(Q)), p.value.count && (!p.value.solo || !s.value || _e())) for (let ne = 1; ne < p.value.count; ne++) {
      const me = Je(de(), { month: se.value(ne - 1), year: j.value(ne - 1) }), Se = Yd(me, { months: 1 });
      c.value[ne] = { month: Be(Se), year: Re(Se) };
    }
  }, oe = (Q, he) => {
    q(Q), P("hours", Wn(Q)), P("minutes", ha(Q)), P("seconds", cr(Q)), p.value.count && he && re();
  }, S = (Q) => {
    if (p.value.count) {
      if (p.value.solo) return 0;
      const he = Be(Q[0]), ne = Be(Q[1]);
      return Math.abs(ne - he) < p.value.count ? 0 : 1;
    }
    return 1;
  }, W = (Q, he) => {
    Q[1] && b.value.showLastInRange ? q(Q[S(Q)], he) : q(Q[0], he);
    const ne = (me, Se) => [me(Q[0]), Q[1] ? me(Q[1]) : d[Se][1]];
    P("hours", ne(Wn, "hours")), P("minutes", ne(ha, "minutes")), P("seconds", ne(cr, "seconds"));
  }, G = (Q, he) => {
    if ((b.value.enabled || e.weekPicker) && !x.value.enabled) return W(Q, he);
    if (x.value.enabled && he) {
      const ne = Q[Q.length - 1];
      return oe(ne, he);
    }
  }, D = (Q) => {
    const he = s.value;
    G(he, Q), p.value.count && p.value.solo && re();
  }, fe = (Q, he) => {
    const ne = Je(de(), { month: se.value(he), year: j.value(he) }), me = Q < 0 ? hn(ne, 1) : pr(ne, 1);
    _(Be(me), Re(me), Q < 0, e.preventMinMaxNavigation) && (V(he, Be(me), Re(me)), t("update-month-year", { instance: he, month: Be(me), year: Re(me) }), p.value.count && !p.value.solo && Ae(he), n());
  }, Ae = (Q) => {
    for (let he = Q - 1; he >= 0; he--) {
      const ne = pr(Je(de(), { month: se.value(he + 1), year: j.value(he + 1) }), 1);
      V(he, Be(ne), Re(ne));
    }
    for (let he = Q + 1; he <= p.value.count - 1; he++) {
      const ne = hn(Je(de(), { month: se.value(he - 1), year: j.value(he - 1) }), 1);
      V(he, Be(ne), Re(ne));
    }
  }, re = () => {
    if (Array.isArray(s.value) && s.value.length === 2) {
      const Q = de(de(s.value[1] ? s.value[1] : hn(s.value[0], 1))), [he, ne] = [Be(s.value[0]), Re(s.value[0])], [me, Se] = [Be(s.value[1]), Re(s.value[1])];
      (he !== me || he === me && ne !== Se) && p.value.solo && V(1, Be(Q), Re(Q));
    } else s.value && !Array.isArray(s.value) && (V(0, Be(s.value), Re(s.value)), q(de()));
  }, Oe = () => {
    e.startDate && (V(0, Be(de(e.startDate)), Re(de(e.startDate))), p.value.count && Ae(0));
  }, O = (Q, he) => {
    if (e.monthChangeOnScroll) {
      const ne = (/* @__PURE__ */ new Date()).getTime() - l.value.getTime(), me = Math.abs(Q.deltaY);
      let Se = 500;
      me > 1 && (Se = 100), me > 100 && (Se = 0), ne > Se && (l.value = /* @__PURE__ */ new Date(), fe(e.monthChangeOnScroll !== "inverse" ? -Q.deltaY : Q.deltaY, he));
    }
  }, m = (Q, he, ne = false) => {
    e.monthChangeOnArrows && e.vertical === ne && k(Q, he);
  }, k = (Q, he) => {
    fe(Q === "right" ? -1 : 1, he);
  }, E = (Q) => {
    if (I.value.markers) return Hl(Q.value, I.value.markers);
  }, B = (Q, he) => {
    switch (e.sixWeeks === true ? "append" : e.sixWeeks) {
      case "prepend":
        return [true, false];
      case "center":
        return [Q == 0, true];
      case "fair":
        return [Q == 0 || he > Q, true];
      case "append":
        return [false, false];
      default:
        return [false, false];
    }
  }, X = (Q, he, ne, me) => {
    if (e.sixWeeks && Q.length < 6) {
      const Se = 6 - Q.length, Mt = (he.getDay() + 7 - me) % 7, Ut = 6 - (ne.getDay() + 7 - me) % 7, [xn, Gt] = B(Mt, Ut);
      for (let Rn = 1; Rn <= Se; Rn++) if (Gt ? !!(Rn % 2) == xn : xn) {
        const an = Q[0].days[0], kr = A(pn(an.value, -7), Be(he));
        Q.unshift({ days: kr });
      } else {
        const an = Q[Q.length - 1], kr = an.days[an.days.length - 1], fl = A(pn(kr.value, 1), Be(he));
        Q.push({ days: fl });
      }
    }
    return Q;
  }, A = (Q, he) => {
    const ne = de(Q), me = [];
    for (let Se = 0; Se < 7; Se++) {
      const Mt = pn(ne, Se), Ut = Be(Mt) !== he;
      me.push({ text: e.hideOffsetDates && Ut ? "" : Mt.getDate(), value: Mt, current: !Ut, classData: {} });
    }
    return me;
  }, U = (Q, he) => {
    const ne = [], me = new Date(he, Q), Se = new Date(he, Q + 1, 0), Mt = e.weekStart, Ut = bn(me, { weekStartsOn: Mt }), xn = (Gt) => {
      const Rn = A(Gt, Q);
      if (ne.push({ days: Rn }), !ne[ne.length - 1].days.some((an) => qe(Wt(an.value), Wt(Se)))) {
        const an = pn(Gt, 7);
        xn(an);
      }
    };
    return xn(Ut), X(ne, me, Se, Mt);
  }, ee = (Q) => {
    const he = fa(de(Q.value), d.hours, d.minutes, Le());
    t("date-update", he), x.value.enabled ? hs(he, s, x.value.limit) : s.value = he, a(), bt().then(() => {
      ce();
    });
  }, le = (Q) => b.value.noDisabledRange ? gp(r.value[0], Q).some((he) => g(he)) : false, ue = () => {
    r.value = s.value ? s.value.slice() : [], r.value.length === 2 && !(b.value.fixedStart || b.value.fixedEnd) && (r.value = []);
  }, ie = (Q, he) => {
    const ne = [de(Q.value), pn(de(Q.value), +b.value.autoRange)];
    R(ne) ? (he && be(Q.value), r.value = ne) : t("invalid-date", Q.value);
  }, be = (Q) => {
    const he = Be(de(Q)), ne = Re(de(Q));
    if (V(0, he, ne), p.value.count > 0) for (let me = 1; me < p.value.count; me++) {
      const Se = Dg(Je(de(Q), { year: se.value(me - 1), month: j.value(me - 1) }));
      V(me, Se.month, Se.year);
    }
  }, xe = (Q) => {
    if (le(Q.value) || !M(Q.value, s.value, b.value.fixedStart ? 0 : 1)) return t("invalid-date", Q.value);
    r.value = Mp(de(Q.value), s, t, b);
  }, De = (Q, he) => {
    if (ue(), b.value.autoRange) return ie(Q, he);
    if (b.value.fixedStart || b.value.fixedEnd) return xe(Q);
    r.value[0] ? M(de(Q.value), s.value) && !le(Q.value) ? pt(de(Q.value), de(r.value[0])) ? (r.value.unshift(de(Q.value)), t("range-end", r.value[0])) : (r.value[1] = de(Q.value), t("range-end", r.value[1])) : (e.autoApply && t("auto-apply-invalid", Q.value), t("invalid-date", Q.value)) : (r.value[0] = de(Q.value), t("range-start", r.value[0]));
  }, Le = (Q = true) => e.enableSeconds ? Array.isArray(d.seconds) ? Q ? d.seconds[0] : d.seconds[1] : d.seconds : 0, je = (Q) => {
    r.value[Q] = fa(r.value[Q], d.hours[Q], d.minutes[Q], Le(Q !== 1));
  }, ze = () => {
    var Q, he;
    r.value[0] && r.value[1] && +((Q = r.value) == null ? void 0 : Q[0]) > +((he = r.value) == null ? void 0 : he[1]) && (r.value.reverse(), t("range-start", r.value[0]), t("range-end", r.value[1]));
  }, dt = () => {
    r.value.length && (r.value[0] && !r.value[1] ? je(0) : (je(0), je(1), a()), ze(), s.value = r.value.slice(), ho(r.value, t, e.autoApply, e.modelAuto));
  }, lt = (Q, he = false) => {
    if (g(Q.value) || !Q.current && e.hideOffsetDates) return t("invalid-date", Q.value);
    if (o.value = JSON.parse(JSON.stringify(Q)), !b.value.enabled) return ee(Q);
    Wu(d.hours) && Wu(d.minutes) && !x.value.enabled && (De(Q, he), dt());
  }, $t = (Q, he) => {
    var ne;
    V(Q, he.month, he.year, true), p.value.count && !p.value.solo && Ae(Q), t("update-month-year", { instance: Q, month: he.month, year: he.year }), n(p.value.solo ? Q : void 0);
    const me = (ne = e.flow) != null && ne.length ? e.flow[e.flowStep] : void 0;
    !he.fromNav && (me === Nt.month || me === Nt.year) && a();
  }, xt = (Q, he) => {
    Cp({ value: Q, modelValue: s, range: b.value.enabled, timezone: he ? void 0 : N.value.timezone }), w(), e.multiCalendars && bt().then(() => ve(true));
  }, cn = () => {
    const Q = ds(de(), N.value);
    b.value.enabled ? s.value && Array.isArray(s.value) && s.value[0] ? s.value = pt(Q, s.value[0]) ? [Q, s.value[0]] : [s.value[0], Q] : s.value = [Q] : s.value = Q, w();
  }, Jt = () => {
    if (Array.isArray(s.value)) if (x.value.enabled) {
      const Q = tt();
      s.value[s.value.length - 1] = Y(Q);
    } else s.value = s.value.map((Q, he) => Q && Y(Q, he));
    else s.value = Y(s.value);
    t("time-update");
  }, tt = () => Array.isArray(s.value) && s.value.length ? s.value[s.value.length - 1] : null;
  return { calendars: c, modelValue: s, month: se, year: j, time: d, disabledTimesConfig: z, today: u, validateTime: H, getCalendarDays: U, getMarker: E, handleScroll: O, handleSwipe: k, handleArrow: m, selectDate: lt, updateMonthYear: $t, presetDate: xt, selectCurrentDate: cn, updateTime: (Q, he = true, ne = false) => {
    C(Q, he, ne, Jt);
  }, assignMonthAndYear: q };
}, Kw = { key: 0 }, Zw = /* @__PURE__ */ Rt({ __name: "DatePicker", props: { ..._n }, emits: ["tooltip-open", "tooltip-close", "mount", "update:internal-model-value", "update-flow-step", "reset-flow", "auto-apply", "focus-menu", "select-date", "range-start", "range-end", "invalid-fixed-range", "time-update", "am-pm-change", "time-picker-open", "time-picker-close", "recalculate-position", "update-month-year", "auto-apply-invalid", "date-update", "invalid-date", "overlay-toggle"], setup(e, { expose: t, emit: n }) {
  const a = n, r = e, { calendars: l, month: o, year: i, modelValue: s, time: c, disabledTimesConfig: d, today: u, validateTime: p, getCalendarDays: v, getMarker: b, handleArrow: h, handleScroll: N, handleSwipe: I, selectDate: x, updateMonthYear: _, presetDate: g, selectCurrentDate: R, updateTime: M, assignMonthAndYear: C } = Hw(r, a, _e, q), Y = za(), { setHoverDate: P, getDayClassData: $, clearHoverDate: H } = uy(s, r), { defaultedMultiCalendars: z } = ct(r), se = te([]), j = te([]), y = te(null), V = tn(Y, "calendar"), w = tn(Y, "monthYear"), ae = tn(Y, "timePicker"), ce = (O) => {
    r.shadow || a("mount", O);
  };
  He(l, () => {
    r.shadow || setTimeout(() => {
      a("recalculate-position");
    }, 0);
  }, { deep: true }), He(z, (O, m) => {
    O.count - m.count > 0 && C();
  }, { deep: true });
  const ve = J(() => (O) => v(o.value(O), i.value(O)).map((m) => ({ ...m, days: m.days.map((k) => (k.marker = b(k), k.classData = $(k), k)) })));
  function _e(O) {
    var m;
    O || O === 0 ? (m = j.value[O]) == null || m.triggerTransition(o.value(O), i.value(O)) : j.value.forEach((k, E) => k.triggerTransition(o.value(E), i.value(E)));
  }
  function q() {
    a("update-flow-step");
  }
  const oe = (O, m = false) => {
    x(O, m), r.spaceConfirm && a("select-date");
  }, S = (O, m, k = 0) => {
    var E;
    (E = se.value[k]) == null || E.toggleMonthPicker(O, m);
  }, W = (O, m, k = 0) => {
    var E;
    (E = se.value[k]) == null || E.toggleYearPicker(O, m);
  }, G = (O, m, k) => {
    var E;
    (E = y.value) == null || E.toggleTimePicker(O, m, k);
  }, D = (O, m) => {
    var k;
    if (!r.range) {
      const E = s.value ? s.value : u, B = m ? new Date(m) : E, X = O ? bn(B, { weekStartsOn: 1 }) : Gd(B, { weekStartsOn: 1 });
      x({ value: X, current: Be(B) === o.value(0), text: "", classData: {} }), (k = document.getElementById(xp(X))) == null || k.focus();
    }
  }, fe = (O) => {
    var m;
    (m = se.value[0]) == null || m.handleMonthYearChange(O, true);
  }, Ae = (O) => {
    _(0, { month: o.value(0), year: i.value(0) + (O ? 1 : -1), fromNav: true });
  }, re = (O, m) => {
    O === Nt.time && a(`time-picker-${m ? "open" : "close"}`), a("overlay-toggle", { open: m, overlay: O });
  }, Oe = (O) => {
    a("overlay-toggle", { open: false, overlay: O }), a("focus-menu");
  };
  return t({ clearHoverDate: H, presetDate: g, selectCurrentDate: R, toggleMonthPicker: S, toggleYearPicker: W, toggleTimePicker: G, handleArrow: h, updateMonthYear: _, getSidebarProps: () => ({ modelValue: s, month: o, year: i, time: c, updateTime: M, updateMonthYear: _, selectDate: x, presetDate: g }), changeMonth: fe, changeYear: Ae, selectWeekDate: D }), (O, m) => (T(), F(Ce, null, [Ne(mo, { "multi-calendars": f(z).count, collapse: O.collapse }, { default: Ie(({ instance: k, index: E }) => [O.disableMonthYearSelect ? Z("", true) : (T(), Pe(Rw, ft({ key: 0, ref: (B) => {
    B && (se.value[E] = B);
  }, months: f(dp)(O.formatLocale, O.locale, O.monthNameFormat), years: f(ps)(O.yearRange, O.locale, O.reverseYears), month: f(o)(k), year: f(i)(k), instance: k }, O.$props, { onMount: m[0] || (m[0] = (B) => ce(f(Ia).header)), onResetFlow: m[1] || (m[1] = (B) => O.$emit("reset-flow")), onUpdateMonthYear: (B) => f(_)(k, B), onOverlayClosed: Oe, onOverlayOpened: m[2] || (m[2] = (B) => O.$emit("overlay-toggle", { open: true, overlay: B })) }), jt({ _: 2 }, [Ve(f(w), (B, X) => ({ name: B, fn: Ie((A) => [ye(O.$slots, B, Ot(Zt(A)))]) }))]), 1040, ["months", "years", "month", "year", "instance", "onUpdateMonthYear"])), Ne(zw, ft({ ref: (B) => {
    B && (j.value[E] = B);
  }, "mapped-dates": ve.value(k), month: f(o)(k), year: f(i)(k), instance: k }, O.$props, { onSelectDate: (B) => f(x)(B, k !== 1), onHandleSpace: (B) => oe(B, k !== 1), onSetHoverDate: m[3] || (m[3] = (B) => f(P)(B)), onHandleScroll: (B) => f(N)(B, k), onHandleSwipe: (B) => f(I)(B, k), onMount: m[4] || (m[4] = (B) => ce(f(Ia).calendar)), onResetFlow: m[5] || (m[5] = (B) => O.$emit("reset-flow")), onTooltipOpen: m[6] || (m[6] = (B) => O.$emit("tooltip-open", B)), onTooltipClose: m[7] || (m[7] = (B) => O.$emit("tooltip-close", B)) }), jt({ _: 2 }, [Ve(f(V), (B, X) => ({ name: B, fn: Ie((A) => [ye(O.$slots, B, Ot(Zt({ ...A })))]) }))]), 1040, ["mapped-dates", "month", "year", "instance", "onSelectDate", "onHandleSpace", "onHandleScroll", "onHandleSwipe"])]), _: 3 }, 8, ["multi-calendars", "collapse"]), O.enableTimePicker ? (T(), F("div", Kw, [O.$slots["time-picker"] ? ye(O.$slots, "time-picker", Ot(ft({ key: 0 }, { time: f(c), updateTime: f(M) }))) : (T(), Pe(Ap, ft({ key: 1, ref_key: "timePickerRef", ref: y }, O.$props, { hours: f(c).hours, minutes: f(c).minutes, seconds: f(c).seconds, "internal-model-value": O.internalModelValue, "disabled-times-config": f(d), "validate-time": f(p), onMount: m[8] || (m[8] = (k) => ce(f(Ia).timePicker)), "onUpdate:hours": m[9] || (m[9] = (k) => f(M)(k)), "onUpdate:minutes": m[10] || (m[10] = (k) => f(M)(k, false)), "onUpdate:seconds": m[11] || (m[11] = (k) => f(M)(k, false, true)), onResetFlow: m[12] || (m[12] = (k) => O.$emit("reset-flow")), onOverlayClosed: m[13] || (m[13] = (k) => re(k, false)), onOverlayOpened: m[14] || (m[14] = (k) => re(k, true)), onAmPmChange: m[15] || (m[15] = (k) => O.$emit("am-pm-change", k)) }), jt({ _: 2 }, [Ve(f(ae), (k, E) => ({ name: k, fn: Ie((B) => [ye(O.$slots, k, Ot(Zt(B)))]) }))]), 1040, ["hours", "minutes", "seconds", "internal-model-value", "disabled-times-config", "validate-time"]))])) : Z("", true)], 64));
} }), Ww = (e, t) => {
  const n = te(), { defaultedMultiCalendars: a, defaultedConfig: r, defaultedHighlight: l, defaultedRange: o, propDates: i, defaultedFilters: s, defaultedMultiDates: c } = ct(e), { modelValue: d, year: u, month: p, calendars: v } = dl(e, t), { isDisabled: b } = Sa(e), { selectYear: h, groupedYears: N, showYearPicker: I, isDisabled: x, toggleYearPicker: _, handleYearSelect: g, handleYear: R } = Tp({ modelValue: d, multiCalendars: a, range: o, highlight: l, calendars: v, propDates: i, month: p, year: u, filters: s, props: e, emit: t }), M = (y, V) => [y, V].map((w) => Ln(w, "MMMM", { locale: e.formatLocale })).join("-"), C = J(() => (y) => d.value ? Array.isArray(d.value) ? d.value.some((V) => Bu(y, V)) : Bu(d.value, y) : false), Y = (y) => {
    if (o.value.enabled) {
      if (Array.isArray(d.value)) {
        const V = qe(y, d.value[0]) || qe(y, d.value[1]);
        return fo(d.value, n.value, y) && !V;
      }
      return false;
    }
    return false;
  }, P = (y, V) => y.quarter === Ru(V) && y.year === Re(V), $ = (y) => typeof l.value == "function" ? l.value({ quarter: Ru(y), year: Re(y) }) : !!l.value.quarters.find((V) => P(V, y)), H = J(() => (y) => {
    const V = Je(/* @__PURE__ */ new Date(), { year: u.value(y) });
    return bm({ start: Jr(V), end: Ud(V) }).map((w) => {
      const ae = rr(w), ce = Eu(w), ve = b(w), _e = Y(ae), q = $(ae);
      return { text: M(ae, ce), value: ae, active: C.value(ae), highlighted: q, disabled: ve, isBetween: _e };
    });
  }), z = (y) => {
    hs(y, d, c.value.limit), t("auto-apply", true);
  }, se = (y) => {
    d.value = gs(d, y, t), ho(d.value, t, e.autoApply, e.modelAuto);
  }, j = (y) => {
    d.value = y, t("auto-apply");
  };
  return { defaultedConfig: r, defaultedMultiCalendars: a, groupedYears: N, year: u, isDisabled: x, quarters: H, showYearPicker: I, modelValue: d, setHoverDate: (y) => {
    n.value = y;
  }, selectYear: h, selectQuarter: (y, V, w) => {
    if (!w) return v.value[V].month = Be(Eu(y)), c.value.enabled ? z(y) : o.value.enabled ? se(y) : j(y);
  }, toggleYearPicker: _, handleYearSelect: g, handleYear: R };
}, Uw = { class: "dp--quarter-items" }, Gw = ["data-test", "disabled", "onClick", "onMouseover"], Qw = /* @__PURE__ */ Rt({ compatConfig: { MODE: 3 }, __name: "QuarterPicker", props: { ..._n }, emits: ["update:internal-model-value", "reset-flow", "overlay-closed", "auto-apply", "range-start", "range-end", "overlay-toggle", "update-month-year"], setup(e, { expose: t, emit: n }) {
  const a = n, r = e, l = za(), o = tn(l, "yearMode"), { defaultedMultiCalendars: i, defaultedConfig: s, groupedYears: c, year: d, isDisabled: u, quarters: p, modelValue: v, showYearPicker: b, setHoverDate: h, selectQuarter: N, toggleYearPicker: I, handleYearSelect: x, handleYear: _ } = Ww(r, a);
  return t({ getSidebarProps: () => ({ modelValue: v, year: d, selectQuarter: N, handleYearSelect: x, handleYear: _ }) }), (g, R) => (T(), Pe(mo, { "multi-calendars": f(i).count, collapse: g.collapse, stretch: "" }, { default: Ie(({ instance: M }) => [L("div", { class: "dp-quarter-picker-wrap", style: Lt({ minHeight: `${f(s).modeHeight}px` }) }, [g.$slots["top-extra"] ? ye(g.$slots, "top-extra", { key: 0, value: g.internalModelValue }) : Z("", true), L("div", null, [Ne(Sp, ft(g.$props, { items: f(c)(M), instance: M, "show-year-picker": f(b)[M], year: f(d)(M), "is-disabled": (C) => f(u)(M, C), onHandleYear: (C) => f(_)(M, C), onYearSelect: (C) => f(x)(C, M), onToggleYearPicker: (C) => f(I)(M, C == null ? void 0 : C.flow, C == null ? void 0 : C.show) }), jt({ _: 2 }, [Ve(f(o), (C, Y) => ({ name: C, fn: Ie((P) => [ye(g.$slots, C, Ot(Zt(P)))]) }))]), 1040, ["items", "instance", "show-year-picker", "year", "is-disabled", "onHandleYear", "onYearSelect", "onToggleYearPicker"])]), L("div", Uw, [(T(true), F(Ce, null, Ve(f(p)(M), (C, Y) => (T(), F("div", { key: Y }, [L("button", { type: "button", class: pe(["dp--qr-btn", { "dp--qr-btn-active": C.active, "dp--qr-btn-between": C.isBetween, "dp--qr-btn-disabled": C.disabled, "dp--highlighted": C.highlighted }]), "data-test": C.value, disabled: C.disabled, onClick: (P) => f(N)(C.value, M, C.disabled), onMouseover: (P) => f(h)(C.value) }, [g.$slots.quarter ? ye(g.$slots, "quarter", { key: 0, value: C.value, text: C.text }) : (T(), F(Ce, { key: 1 }, [Ge(ge(C.text), 1)], 64))], 42, Gw)]))), 128))])], 4)]), _: 3 }, 8, ["multi-calendars", "collapse"]));
} }), Xw = ["id", "aria-label"], Jw = { key: 0, class: "dp--menu-load-container" }, ey = L("span", { class: "dp--menu-loader" }, null, -1), ty = [ey], ny = { key: 0, class: "dp__sidebar_left" }, ay = ["data-test", "onClick", "onKeydown"], ry = { key: 2, class: "dp__sidebar_right" }, ly = { key: 3, class: "dp__action_extra" }, Uu = /* @__PURE__ */ Rt({ compatConfig: { MODE: 3 }, __name: "DatepickerMenu", props: { ...vo, shadow: { type: Boolean, default: false }, openOnTop: { type: Boolean, default: false }, internalModelValue: { type: [Date, Array], default: null }, noOverlayFocus: { type: Boolean, default: false }, collapse: { type: Boolean, default: false }, getInputRect: { type: Function, default: () => ({}) }, isTextInputDate: { type: Boolean, default: false } }, emits: ["close-picker", "select-date", "auto-apply", "time-update", "flow-step", "update-month-year", "invalid-select", "update:internal-model-value", "recalculate-position", "invalid-fixed-range", "tooltip-open", "tooltip-close", "time-picker-open", "time-picker-close", "am-pm-change", "range-start", "range-end", "auto-apply-invalid", "date-update", "invalid-date", "overlay-toggle"], setup(e, { expose: t, emit: n }) {
  const a = n, r = e, l = te(null), o = J(() => {
    const { openOnTop: A, ...U } = r;
    return { ...U, flowStep: P.value, collapse: r.collapse, noOverlayFocus: r.noOverlayFocus, menuWrapRef: l.value };
  }), { setMenuFocused: i, setShiftKey: s, control: c } = kp(), d = za(), { defaultedTextInput: u, defaultedInline: p, defaultedConfig: v, defaultedUI: b } = ct(r), h = te(null), N = te(0), I = te(null), x = te(false), _ = te(null);
  ot(() => {
    if (!r.shadow) {
      x.value = true, g(), window.addEventListener("resize", g);
      const A = kt(l);
      if (A && !u.value.enabled && !p.value.enabled && (i(true), V()), A) {
        const U = (ee) => {
          v.value.allowPreventDefault && ee.preventDefault(), pa(ee, v.value, true);
        };
        A.addEventListener("pointerdown", U), A.addEventListener("mousedown", U);
      }
    }
  }), br(() => {
    window.removeEventListener("resize", g);
  });
  const g = () => {
    const A = kt(I);
    A && (N.value = A.getBoundingClientRect().width);
  }, { arrowRight: R, arrowLeft: M, arrowDown: C, arrowUp: Y } = ka(), { flowStep: P, updateFlowStep: $, childMount: H, resetFlow: z, handleFlow: se } = cy(r, a, _), j = J(() => r.monthPicker ? uw : r.yearPicker ? dw : r.timePicker ? Aw : r.quarterPicker ? Qw : Zw), y = J(() => {
    var A;
    if (v.value.arrowLeft) return v.value.arrowLeft;
    const U = (A = l.value) == null ? void 0 : A.getBoundingClientRect(), ee = r.getInputRect();
    return (ee == null ? void 0 : ee.width) < (N == null ? void 0 : N.value) && (ee == null ? void 0 : ee.left) <= ((U == null ? void 0 : U.left) ?? 0) ? `${(ee == null ? void 0 : ee.width) / 2}px` : (ee == null ? void 0 : ee.right) >= ((U == null ? void 0 : U.right) ?? 0) && (ee == null ? void 0 : ee.width) < (N == null ? void 0 : N.value) ? `${(N == null ? void 0 : N.value) - (ee == null ? void 0 : ee.width) / 2}px` : "50%";
  }), V = () => {
    const A = kt(l);
    A && A.focus({ preventScroll: true });
  }, w = J(() => {
    var A;
    return ((A = _.value) == null ? void 0 : A.getSidebarProps()) || {};
  }), ae = () => {
    r.openOnTop && a("recalculate-position");
  }, ce = tn(d, "action"), ve = J(() => r.monthPicker || r.yearPicker ? tn(d, "monthYear") : r.timePicker ? tn(d, "timePicker") : tn(d, "shared")), _e = J(() => r.openOnTop ? "dp__arrow_bottom" : "dp__arrow_top"), q = J(() => ({ dp__menu_disabled: r.disabled, dp__menu_readonly: r.readonly, "dp-menu-loading": r.loading })), oe = J(() => ({ dp__menu: true, dp__menu_index: !p.value.enabled, dp__relative: p.value.enabled, [r.menuClassName]: !!r.menuClassName, ...b.value.menu ?? {} })), S = (A) => {
    pa(A, v.value, true);
  }, W = () => {
    r.escClose && a("close-picker");
  }, G = (A) => {
    if (r.arrowNavigation) {
      if (A === zt.up) return Y();
      if (A === zt.down) return C();
      if (A === zt.left) return M();
      if (A === zt.right) return R();
    } else A === zt.left || A === zt.up ? Oe("handleArrow", zt.left, 0, A === zt.up) : Oe("handleArrow", zt.right, 0, A === zt.down);
  }, D = (A) => {
    s(A.shiftKey), !r.disableMonthYearSelect && A.code === it.tab && A.target.classList.contains("dp__menu") && c.value.shiftKeyInMenu && (A.preventDefault(), pa(A, v.value, true), a("close-picker"));
  }, fe = () => {
    V(), a("time-picker-close");
  }, Ae = (A) => {
    var U, ee, le;
    (U = _.value) == null || U.toggleTimePicker(false, false), (ee = _.value) == null || ee.toggleMonthPicker(false, false, A), (le = _.value) == null || le.toggleYearPicker(false, false, A);
  }, re = (A, U = 0) => {
    var ee, le, ue;
    return A === "month" ? (ee = _.value) == null ? void 0 : ee.toggleMonthPicker(false, true, U) : A === "year" ? (le = _.value) == null ? void 0 : le.toggleYearPicker(false, true, U) : A === "time" ? (ue = _.value) == null ? void 0 : ue.toggleTimePicker(true, false) : Ae(U);
  }, Oe = (A, ...U) => {
    var ee, le;
    (ee = _.value) != null && ee[A] && ((le = _.value) == null || le[A](...U));
  }, O = () => {
    Oe("selectCurrentDate");
  }, m = (A, U) => {
    Oe("presetDate", A, U);
  }, k = () => {
    Oe("clearHoverDate");
  }, E = (A, U) => {
    Oe("updateMonthYear", A, U);
  }, B = (A, U) => {
    A.preventDefault(), G(U);
  }, X = (A) => {
    var U;
    if (D(A), A.key === it.home || A.key === it.end) return Oe("selectWeekDate", A.key === it.home, A.target.getAttribute("id"));
    switch ((A.key === it.pageUp || A.key === it.pageDown) && (A.shiftKey ? Oe("changeYear", A.key === it.pageUp) : Oe("changeMonth", A.key === it.pageUp), A.target.getAttribute("id") && ((U = l.value) == null || U.focus({ preventScroll: true }))), A.key) {
      case it.esc:
        return W();
      case it.arrowLeft:
        return B(A, zt.left);
      case it.arrowRight:
        return B(A, zt.right);
      case it.arrowUp:
        return B(A, zt.up);
      case it.arrowDown:
        return B(A, zt.down);
      default:
        return;
    }
  };
  return t({ updateMonthYear: E, switchView: re, handleFlow: se }), (A, U) => {
    var ee, le, ue;
    return T(), F("div", { id: A.uid ? `dp-menu-${A.uid}` : void 0, ref_key: "dpMenuRef", ref: l, tabindex: "0", role: "dialog", "aria-label": (ee = A.ariaLabels) == null ? void 0 : ee.menu, class: pe(oe.value), style: Lt({ "--dp-arrow-left": y.value }), onMouseleave: k, onClick: S, onKeydown: X }, [(A.disabled || A.readonly) && f(p).enabled || A.loading ? (T(), F("div", { key: 0, class: pe(q.value) }, [A.loading ? (T(), F("div", Jw, ty)) : Z("", true)], 2)) : Z("", true), !f(p).enabled && !A.teleportCenter ? (T(), F("div", { key: 1, class: pe(_e.value) }, null, 2)) : Z("", true), L("div", { ref_key: "innerMenuRef", ref: I, class: pe({ dp__menu_content_wrapper: ((le = A.presetDates) == null ? void 0 : le.length) || !!A.$slots["left-sidebar"] || !!A.$slots["right-sidebar"], "dp--menu-content-wrapper-collapsed": e.collapse && (((ue = A.presetDates) == null ? void 0 : ue.length) || !!A.$slots["left-sidebar"] || !!A.$slots["right-sidebar"]) }), style: Lt({ "--dp-menu-width": `${N.value}px` }) }, [A.$slots["left-sidebar"] ? (T(), F("div", ny, [ye(A.$slots, "left-sidebar", Ot(Zt(w.value)))])) : Z("", true), A.presetDates.length ? (T(), F("div", { key: 1, class: pe({ "dp--preset-dates-collapsed": e.collapse, "dp--preset-dates": true }) }, [(T(true), F(Ce, null, Ve(A.presetDates, (ie, be) => (T(), F(Ce, { key: be }, [ie.slot ? ye(A.$slots, ie.slot, { key: 0, presetDate: m, label: ie.label, value: ie.value }) : (T(), F("button", { key: 1, type: "button", style: Lt(ie.style || {}), class: pe(["dp__btn dp--preset-range", { "dp--preset-range-collapsed": e.collapse }]), "data-test": ie.testId ?? void 0, onClick: da((xe) => m(ie.value, ie.noTz), ["prevent"]), onKeydown: (xe) => f(Qt)(xe, () => m(ie.value, ie.noTz), true) }, ge(ie.label), 47, ay))], 64))), 128))], 2)) : Z("", true), L("div", { ref_key: "calendarWrapperRef", ref: h, class: "dp__instance_calendar", role: "document" }, [(T(), Pe(ol(j.value), ft({ ref_key: "dynCmpRef", ref: _ }, o.value, { "flow-step": f(P), onMount: f(H), onUpdateFlowStep: f($), onResetFlow: f(z), onFocusMenu: V, onSelectDate: U[0] || (U[0] = (ie) => A.$emit("select-date")), onDateUpdate: U[1] || (U[1] = (ie) => A.$emit("date-update", ie)), onTooltipOpen: U[2] || (U[2] = (ie) => A.$emit("tooltip-open", ie)), onTooltipClose: U[3] || (U[3] = (ie) => A.$emit("tooltip-close", ie)), onAutoApply: U[4] || (U[4] = (ie) => A.$emit("auto-apply", ie)), onRangeStart: U[5] || (U[5] = (ie) => A.$emit("range-start", ie)), onRangeEnd: U[6] || (U[6] = (ie) => A.$emit("range-end", ie)), onInvalidFixedRange: U[7] || (U[7] = (ie) => A.$emit("invalid-fixed-range", ie)), onTimeUpdate: U[8] || (U[8] = (ie) => A.$emit("time-update")), onAmPmChange: U[9] || (U[9] = (ie) => A.$emit("am-pm-change", ie)), onTimePickerOpen: U[10] || (U[10] = (ie) => A.$emit("time-picker-open", ie)), onTimePickerClose: fe, onRecalculatePosition: ae, onUpdateMonthYear: U[11] || (U[11] = (ie) => A.$emit("update-month-year", ie)), onAutoApplyInvalid: U[12] || (U[12] = (ie) => A.$emit("auto-apply-invalid", ie)), onInvalidDate: U[13] || (U[13] = (ie) => A.$emit("invalid-date", ie)), onOverlayToggle: U[14] || (U[14] = (ie) => A.$emit("overlay-toggle", ie)), "onUpdate:internalModelValue": U[15] || (U[15] = (ie) => A.$emit("update:internal-model-value", ie)) }), jt({ _: 2 }, [Ve(ve.value, (ie, be) => ({ name: ie, fn: Ie((xe) => [ye(A.$slots, ie, Ot(Zt({ ...xe })))]) }))]), 1040, ["flow-step", "onMount", "onUpdateFlowStep", "onResetFlow"]))], 512), A.$slots["right-sidebar"] ? (T(), F("div", ry, [ye(A.$slots, "right-sidebar", Ot(Zt(w.value)))])) : Z("", true), A.$slots["action-extra"] ? (T(), F("div", ly, [A.$slots["action-extra"] ? ye(A.$slots, "action-extra", { key: 0, selectCurrentDate: O }) : Z("", true)])) : Z("", true)], 6), !A.autoApply || f(v).keepActionRow ? (T(), Pe(tw, ft({ key: 2, "menu-mount": x.value }, o.value, { "calendar-width": N.value, onClosePicker: U[16] || (U[16] = (ie) => A.$emit("close-picker")), onSelectDate: U[17] || (U[17] = (ie) => A.$emit("select-date")), onInvalidSelect: U[18] || (U[18] = (ie) => A.$emit("invalid-select")), onSelectNow: O }), jt({ _: 2 }, [Ve(f(ce), (ie, be) => ({ name: ie, fn: Ie((xe) => [ye(A.$slots, ie, Ot(Zt({ ...xe })))]) }))]), 1040, ["menu-mount", "calendar-width"])) : Z("", true)], 46, Xw);
  };
} });
var Ja = ((e) => (e.center = "center", e.left = "left", e.right = "right", e))(Ja || {});
const oy = ({ menuRef: e, menuRefInner: t, inputRef: n, pickerWrapperRef: a, inline: r, emit: l, props: o, slots: i }) => {
  const s = te({}), c = te(false), d = te({ top: "0", left: "0" }), u = te(false), p = ir(o, "teleportCenter");
  He(p, () => {
    d.value = JSON.parse(JSON.stringify({})), g();
  });
  const v = (y) => {
    if (o.teleport) {
      const V = y.getBoundingClientRect();
      return { left: V.left + window.scrollX, top: V.top + window.scrollY };
    }
    return { top: 0, left: 0 };
  }, b = (y, V) => {
    d.value.left = `${y + V - s.value.width}px`;
  }, h = (y) => {
    d.value.left = `${y}px`;
  }, N = (y, V) => {
    o.position === Ja.left && h(y), o.position === Ja.right && b(y, V), o.position === Ja.center && (d.value.left = `${y + V / 2 - s.value.width / 2}px`);
  }, I = (y) => {
    const { width: V, height: w } = y.getBoundingClientRect(), { top: ae, left: ce } = o.altPosition ? o.altPosition(y) : v(y);
    return { top: +ae, left: +ce, width: V, height: w };
  }, x = () => {
    d.value.left = "50%", d.value.top = "50%", d.value.transform = "translate(-50%, -50%)", d.value.position = "fixed", delete d.value.opacity;
  }, _ = () => {
    const y = kt(n), { top: V, left: w, transform: ae } = o.altPosition(y);
    d.value = { top: `${V}px`, left: `${w}px`, transform: ae ?? "" };
  }, g = (y = true) => {
    var V;
    if (!r.value.enabled) {
      if (p.value) return x();
      if (o.altPosition !== null) return _();
      if (y) {
        const w = o.teleport ? (V = t.value) == null ? void 0 : V.$el : e.value;
        w && (s.value = w.getBoundingClientRect()), l("recalculate-position");
      }
      return H();
    }
  }, R = ({ inputEl: y, left: V, width: w }) => {
    window.screen.width > 768 && !c.value && N(V, w), Y(y);
  }, M = (y) => {
    const { top: V, left: w, height: ae, width: ce } = I(y);
    d.value.top = `${ae + V + +o.offset}px`, u.value = false, c.value || (d.value.left = `${w + ce / 2 - s.value.width / 2}px`), R({ inputEl: y, left: w, width: ce });
  }, C = (y) => {
    const { top: V, left: w, width: ae } = I(y);
    d.value.top = `${V - +o.offset - s.value.height}px`, u.value = true, R({ inputEl: y, left: w, width: ae });
  }, Y = (y) => {
    if (o.autoPosition) {
      const { left: V, width: w } = I(y), { left: ae, right: ce } = s.value;
      if (!c.value) {
        if (Math.abs(ae) !== Math.abs(ce)) {
          if (ae <= 0) return c.value = true, h(V);
          if (ce >= document.documentElement.clientWidth) return c.value = true, b(V, w);
        }
        return N(V, w);
      }
    }
  }, P = () => {
    const y = kt(n);
    if (y) {
      const { height: V } = s.value, { top: w, height: ae } = y.getBoundingClientRect(), ce = window.innerHeight - w - ae, ve = w;
      return V <= ce ? $a.bottom : V > ce && V <= ve ? $a.top : ce >= ve ? $a.bottom : $a.top;
    }
    return $a.bottom;
  }, $ = (y) => P() === $a.bottom ? M(y) : C(y), H = () => {
    const y = kt(n);
    if (y) return o.autoPosition ? $(y) : M(y);
  }, z = function(y) {
    if (y) {
      const V = y.scrollHeight > y.clientHeight, w = window.getComputedStyle(y).overflowY.indexOf("hidden") !== -1;
      return V && !w;
    }
    return true;
  }, se = function(y) {
    return !y || y === document.body || y.nodeType === Node.DOCUMENT_FRAGMENT_NODE ? window : z(y) ? y : se(y.assignedSlot ? y.assignedSlot.parentNode : y.parentNode);
  }, j = (y) => {
    if (y) switch (o.position) {
      case Ja.left:
        return { left: 0, transform: "translateX(0)" };
      case Ja.right:
        return { left: `${y.width}px`, transform: "translateX(-100%)" };
      default:
        return { left: `${y.width / 2}px`, transform: "translateX(-50%)" };
    }
    return {};
  };
  return { openOnTop: u, menuStyle: d, xCorrect: c, setMenuPosition: g, getScrollableParent: se, shadowRender: (y, V) => {
    var w, ae, ce;
    const ve = document.createElement("div"), _e = (w = kt(n)) == null ? void 0 : w.getBoundingClientRect();
    ve.setAttribute("id", "dp--temp-container");
    const q = (ae = a.value) != null && ae.clientWidth ? a.value : document.body;
    q.append(ve);
    const oe = j(_e), S = xd(y, { ...V, shadow: true, style: { opacity: 0, position: "absolute", ...oe } }, Object.fromEntries(Object.keys(i).filter((W) => ["right-sidebar", "left-sidebar", "top-extra", "action-extra"].includes(W)).map((W) => [W, i[W]])));
    mu(S, ve), s.value = (ce = S.el) == null ? void 0 : ce.getBoundingClientRect(), mu(null, ve), q.removeChild(ve);
  } };
}, ta = [{ name: "clock-icon", use: ["time", "calendar", "shared"] }, { name: "arrow-left", use: ["month-year", "calendar", "shared", "year-mode"] }, { name: "arrow-right", use: ["month-year", "calendar", "shared", "year-mode"] }, { name: "arrow-up", use: ["time", "calendar", "month-year", "shared"] }, { name: "arrow-down", use: ["time", "calendar", "month-year", "shared"] }, { name: "calendar-icon", use: ["month-year", "time", "calendar", "shared", "year-mode"] }, { name: "day", use: ["calendar", "shared"] }, { name: "month-overlay-value", use: ["calendar", "month-year", "shared"] }, { name: "year-overlay-value", use: ["calendar", "month-year", "shared", "year-mode"] }, { name: "year-overlay", use: ["month-year", "shared"] }, { name: "month-overlay", use: ["month-year", "shared"] }, { name: "month-overlay-header", use: ["month-year", "shared"] }, { name: "year-overlay-header", use: ["month-year", "shared"] }, { name: "hours-overlay-value", use: ["calendar", "time", "shared"] }, { name: "hours-overlay-header", use: ["calendar", "time", "shared"] }, { name: "minutes-overlay-value", use: ["calendar", "time", "shared"] }, { name: "minutes-overlay-header", use: ["calendar", "time", "shared"] }, { name: "seconds-overlay-value", use: ["calendar", "time", "shared"] }, { name: "seconds-overlay-header", use: ["calendar", "time", "shared"] }, { name: "hours", use: ["calendar", "time", "shared"] }, { name: "minutes", use: ["calendar", "time", "shared"] }, { name: "month", use: ["calendar", "month-year", "shared"] }, { name: "year", use: ["calendar", "month-year", "shared", "year-mode"] }, { name: "action-buttons", use: ["action"] }, { name: "action-preview", use: ["action"] }, { name: "calendar-header", use: ["calendar", "shared"] }, { name: "marker-tooltip", use: ["calendar", "shared"] }, { name: "action-extra", use: ["menu"] }, { name: "time-picker-overlay", use: ["calendar", "time", "shared"] }, { name: "am-pm-button", use: ["calendar", "time", "shared"] }, { name: "left-sidebar", use: ["menu"] }, { name: "right-sidebar", use: ["menu"] }, { name: "month-year", use: ["month-year", "shared"] }, { name: "time-picker", use: ["menu", "shared"] }, { name: "action-row", use: ["action"] }, { name: "marker", use: ["calendar", "shared"] }, { name: "quarter", use: ["shared"] }, { name: "top-extra", use: ["shared", "month-year"] }, { name: "tp-inline-arrow-up", use: ["shared", "time"] }, { name: "tp-inline-arrow-down", use: ["shared", "time"] }], iy = [{ name: "trigger" }, { name: "input-icon" }, { name: "clear-icon" }, { name: "dp-input" }], sy = { all: () => ta, monthYear: () => ta.filter((e) => e.use.includes("month-year")), input: () => iy, timePicker: () => ta.filter((e) => e.use.includes("time")), action: () => ta.filter((e) => e.use.includes("action")), calendar: () => ta.filter((e) => e.use.includes("calendar")), menu: () => ta.filter((e) => e.use.includes("menu")), shared: () => ta.filter((e) => e.use.includes("shared")), yearMode: () => ta.filter((e) => e.use.includes("year-mode")) }, tn = (e, t, n) => {
  const a = [];
  return sy[t]().forEach((r) => {
    e[r.name] && a.push(r.name);
  }), n != null && n.length && n.forEach((r) => {
    r.slot && a.push(r.slot);
  }), a;
}, cl = (e) => {
  const t = J(() => (a) => e.value ? a ? e.value.open : e.value.close : ""), n = J(() => (a) => e.value ? a ? e.value.menuAppearTop : e.value.menuAppearBottom : "");
  return { transitionName: t, showTransition: !!e.value, menuTransition: n };
}, dl = (e, t, n) => {
  const { defaultedRange: a, defaultedTz: r } = ct(e), l = de(Xt(de(), r.value.timezone)), o = te([{ month: Be(l), year: Re(l) }]), i = (p) => {
    const v = { hours: Wn(l), minutes: ha(l), seconds: 0 };
    return a.value.enabled ? [v[p], v[p]] : v[p];
  }, s = un({ hours: i("hours"), minutes: i("minutes"), seconds: i("seconds") });
  He(a, (p, v) => {
    p.enabled !== v.enabled && (s.hours = i("hours"), s.minutes = i("minutes"), s.seconds = i("seconds"));
  }, { deep: true });
  const c = J({ get: () => e.internalModelValue, set: (p) => {
    !e.readonly && !e.disabled && t("update:internal-model-value", p);
  } }), d = J(() => (p) => o.value[p] ? o.value[p].month : 0), u = J(() => (p) => o.value[p] ? o.value[p].year : 0);
  return He(c, (p, v) => {
    n && JSON.stringify(p ?? {}) !== JSON.stringify(v ?? {}) && n();
  }, { deep: true }), { calendars: o, time: s, modelValue: c, month: d, year: u, today: l };
}, uy = (e, t) => {
  const { defaultedMultiCalendars: n, defaultedMultiDates: a, defaultedUI: r, defaultedHighlight: l, defaultedTz: o, propDates: i, defaultedRange: s } = ct(t), { isDisabled: c } = Sa(t), d = te(null), u = te(Xt(/* @__PURE__ */ new Date(), o.value.timezone)), p = (S) => {
    !S.current && t.hideOffsetDates || (d.value = S.value);
  }, v = () => {
    d.value = null;
  }, b = (S) => Array.isArray(e.value) && s.value.enabled && e.value[0] && d.value ? S ? gt(d.value, e.value[0]) : pt(d.value, e.value[0]) : true, h = (S, W) => {
    const G = () => e.value ? W ? e.value[0] || null : e.value[1] : null, D = e.value && Array.isArray(e.value) ? G() : null;
    return qe(de(S.value), D);
  }, N = (S) => {
    const W = Array.isArray(e.value) ? e.value[0] : null;
    return S ? !pt(d.value ?? null, W) : true;
  }, I = (S, W = true) => (s.value.enabled || t.weekPicker) && Array.isArray(e.value) && e.value.length === 2 ? t.hideOffsetDates && !S.current ? false : qe(de(S.value), e.value[W ? 0 : 1]) : s.value.enabled ? h(S, W) && N(W) || qe(S.value, Array.isArray(e.value) ? e.value[0] : null) && b(W) : false, x = (S, W) => {
    if (Array.isArray(e.value) && e.value[0] && e.value.length === 1) {
      const G = qe(S.value, d.value);
      return W ? gt(e.value[0], S.value) && G : pt(e.value[0], S.value) && G;
    }
    return false;
  }, _ = (S) => !e.value || t.hideOffsetDates && !S.current ? false : s.value.enabled ? t.modelAuto && Array.isArray(e.value) ? qe(S.value, e.value[0] ? e.value[0] : u.value) : false : a.value.enabled && Array.isArray(e.value) ? e.value.some((W) => qe(W, S.value)) : qe(S.value, e.value ? e.value : u.value), g = (S) => {
    if (s.value.autoRange || t.weekPicker) {
      if (d.value) {
        if (t.hideOffsetDates && !S.current) return false;
        const W = pn(d.value, +s.value.autoRange), G = jn(de(d.value), t.weekStart);
        return t.weekPicker ? qe(G[1], de(S.value)) : qe(W, de(S.value));
      }
      return false;
    }
    return false;
  }, R = (S) => {
    if (s.value.autoRange || t.weekPicker) {
      if (d.value) {
        const W = pn(d.value, +s.value.autoRange);
        if (t.hideOffsetDates && !S.current) return false;
        const G = jn(de(d.value), t.weekStart);
        return t.weekPicker ? gt(S.value, G[0]) && pt(S.value, G[1]) : gt(S.value, d.value) && pt(S.value, W);
      }
      return false;
    }
    return false;
  }, M = (S) => {
    if (s.value.autoRange || t.weekPicker) {
      if (d.value) {
        if (t.hideOffsetDates && !S.current) return false;
        const W = jn(de(d.value), t.weekStart);
        return t.weekPicker ? qe(W[0], S.value) : qe(d.value, S.value);
      }
      return false;
    }
    return false;
  }, C = (S) => fo(e.value, d.value, S.value), Y = () => t.modelAuto && Array.isArray(t.internalModelValue) ? !!t.internalModelValue[0] : false, P = () => t.modelAuto ? pp(t.internalModelValue) : true, $ = (S) => {
    if (t.weekPicker) return false;
    const W = s.value.enabled ? !I(S) && !I(S, false) : true;
    return !c(S.value) && !_(S) && !(!S.current && t.hideOffsetDates) && W;
  }, H = (S) => s.value.enabled ? t.modelAuto ? Y() && _(S) : false : _(S), z = (S) => l.value ? Mg(S.value, i.value.highlight) : false, se = (S) => {
    const W = c(S.value);
    return W && (typeof l.value == "function" ? !l.value(S.value, W) : !l.value.options.highlightDisabled);
  }, j = (S) => {
    var W;
    return typeof l.value == "function" ? l.value(S.value) : (W = l.value.weekdays) == null ? void 0 : W.includes(S.value.getDay());
  }, y = (S) => (s.value.enabled || t.weekPicker) && (!(n.value.count > 0) || S.current) && P() && !(!S.current && t.hideOffsetDates) && !_(S) ? C(S) : false, V = (S) => {
    const { isRangeStart: W, isRangeEnd: G } = ve(S), D = s.value.enabled ? W || G : false;
    return { dp__cell_offset: !S.current, dp__pointer: !t.disabled && !(!S.current && t.hideOffsetDates) && !c(S.value), dp__cell_disabled: c(S.value), dp__cell_highlight: !se(S) && (z(S) || j(S)) && !H(S) && !D && !M(S) && !(y(S) && t.weekPicker) && !G, dp__cell_highlight_active: !se(S) && (z(S) || j(S)) && H(S), dp__today: !t.noToday && qe(S.value, u.value) && S.current, "dp--past": pt(S.value, u.value), "dp--future": gt(S.value, u.value) };
  }, w = (S) => ({ dp__active_date: H(S), dp__date_hover: $(S) }), ae = (S) => {
    if (e.value && !Array.isArray(e.value)) {
      const W = jn(e.value, t.weekStart);
      return { ...q(S), dp__range_start: qe(W[0], S.value), dp__range_end: qe(W[1], S.value), dp__range_between_week: gt(S.value, W[0]) && pt(S.value, W[1]) };
    }
    return { ...q(S) };
  }, ce = (S) => {
    if (e.value && Array.isArray(e.value)) {
      const W = jn(e.value[0], t.weekStart), G = e.value[1] ? jn(e.value[1], t.weekStart) : [];
      return { ...q(S), dp__range_start: qe(W[0], S.value) || qe(G[0], S.value), dp__range_end: qe(W[1], S.value) || qe(G[1], S.value), dp__range_between_week: gt(S.value, W[0]) && pt(S.value, W[1]) || gt(S.value, G[0]) && pt(S.value, G[1]), dp__range_between: gt(S.value, W[1]) && pt(S.value, G[0]) };
    }
    return { ...q(S) };
  }, ve = (S) => {
    const W = n.value.count > 0 ? S.current && I(S) && P() : I(S) && P(), G = n.value.count > 0 ? S.current && I(S, false) && P() : I(S, false) && P();
    return { isRangeStart: W, isRangeEnd: G };
  }, _e = (S) => {
    const { isRangeStart: W, isRangeEnd: G } = ve(S);
    return { dp__range_start: W, dp__range_end: G, dp__range_between: y(S), dp__date_hover: qe(S.value, d.value) && !W && !G && !t.weekPicker, dp__date_hover_start: x(S, true), dp__date_hover_end: x(S, false) };
  }, q = (S) => ({ ..._e(S), dp__cell_auto_range: R(S), dp__cell_auto_range_start: M(S), dp__cell_auto_range_end: g(S) }), oe = (S) => s.value.enabled ? s.value.autoRange ? q(S) : t.modelAuto ? { ...w(S), ..._e(S) } : t.weekPicker ? ce(S) : _e(S) : t.weekPicker ? ae(S) : w(S);
  return { setHoverDate: p, clearHoverDate: v, getDayClassData: (S) => t.hideOffsetDates && !S.current ? {} : { ...V(S), ...oe(S), [t.dayClass ? t.dayClass(S.value, t.internalModelValue) : ""]: true, [t.calendarCellClassName]: !!t.calendarCellClassName, ...r.value.calendarCell ?? {} } };
}, Sa = (e) => {
  const { defaultedFilters: t, defaultedRange: n, propDates: a, defaultedMultiDates: r } = ct(e), l = (j) => a.value.disabledDates ? typeof a.value.disabledDates == "function" ? a.value.disabledDates(de(j)) : !!Hl(j, a.value.disabledDates) : false, o = (j) => a.value.maxDate ? e.yearPicker ? Re(j) > Re(a.value.maxDate) : gt(j, a.value.maxDate) : false, i = (j) => a.value.minDate ? e.yearPicker ? Re(j) < Re(a.value.minDate) : pt(j, a.value.minDate) : false, s = (j) => {
    const y = o(j), V = i(j), w = l(j), ae = t.value.months.map((oe) => +oe).includes(Be(j)), ce = e.disabledWeekDays.length ? e.disabledWeekDays.some((oe) => +oe === fh(j)) : false, ve = v(j), _e = Re(j), q = _e < +e.yearRange[0] || _e > +e.yearRange[1];
    return !(y || V || w || ae || q || ce || ve);
  }, c = (j, y) => pt(...ua(a.value.minDate, j, y)) || qe(...ua(a.value.minDate, j, y)), d = (j, y) => gt(...ua(a.value.maxDate, j, y)) || qe(...ua(a.value.maxDate, j, y)), u = (j, y, V) => {
    let w = false;
    return a.value.maxDate && V && d(j, y) && (w = true), a.value.minDate && !V && c(j, y) && (w = true), w;
  }, p = (j, y, V, w) => {
    let ae = false;
    return w ? a.value.minDate && a.value.maxDate ? ae = u(j, y, V) : (a.value.minDate && c(j, y) || a.value.maxDate && d(j, y)) && (ae = true) : ae = true, ae;
  }, v = (j) => Array.isArray(a.value.allowedDates) && !a.value.allowedDates.length ? true : a.value.allowedDates ? !Hl(j, a.value.allowedDates) : false, b = (j) => !s(j), h = (j) => n.value.noDisabledRange ? !Wd({ start: j[0], end: j[1] }).some((y) => b(y)) : true, N = (j) => {
    if (j) {
      const y = Re(j);
      return y >= +e.yearRange[0] && y <= e.yearRange[1];
    }
    return true;
  }, I = (j, y) => !!(Array.isArray(j) && j[y] && (n.value.maxRange || n.value.minRange) && N(j[y])), x = (j, y, V = 0) => {
    if (I(y, V) && N(j)) {
      const w = Kd(j, y[V]), ae = gp(y[V], j), ce = ae.length === 1 ? 0 : ae.filter((_e) => b(_e)).length, ve = Math.abs(w) - (n.value.minMaxRawRange ? 0 : ce);
      if (n.value.minRange && n.value.maxRange) return ve >= +n.value.minRange && ve <= +n.value.maxRange;
      if (n.value.minRange) return ve >= +n.value.minRange;
      if (n.value.maxRange) return ve <= +n.value.maxRange;
    }
    return true;
  }, _ = () => !e.enableTimePicker || e.monthPicker || e.yearPicker || e.ignoreTimeValidation, g = (j) => Array.isArray(j) ? [j[0] ? Zo(j[0]) : null, j[1] ? Zo(j[1]) : null] : Zo(j), R = (j, y, V) => j.find((w) => +w.hours === Wn(y) && w.minutes === "*" ? true : +w.minutes === ha(y) && +w.hours === Wn(y)) && V, M = (j, y, V) => {
    const [w, ae] = j, [ce, ve] = y;
    return !R(w, ce, V) && !R(ae, ve, V) && V;
  }, C = (j, y) => {
    const V = Array.isArray(y) ? y : [y];
    return Array.isArray(e.disabledTimes) ? Array.isArray(e.disabledTimes[0]) ? M(e.disabledTimes, V, j) : !V.some((w) => R(e.disabledTimes, w, j)) : j;
  }, Y = (j, y) => {
    const V = Array.isArray(y) ? [Va(y[0]), y[1] ? Va(y[1]) : void 0] : Va(y), w = !e.disabledTimes(V);
    return j && w;
  }, P = (j, y) => e.disabledTimes ? Array.isArray(e.disabledTimes) ? C(y, j) : Y(y, j) : y, $ = (j) => {
    let y = true;
    if (!j || _()) return true;
    const V = !a.value.minDate && !a.value.maxDate ? g(j) : j;
    return (e.maxTime || a.value.maxDate) && (y = Hu(e.maxTime, a.value.maxDate, "max", Tt(V), y)), (e.minTime || a.value.minDate) && (y = Hu(e.minTime, a.value.minDate, "min", Tt(V), y)), P(j, y);
  }, H = (j) => {
    if (!e.monthPicker) return true;
    let y = true;
    const V = de(fn(j));
    if (a.value.minDate && a.value.maxDate) {
      const w = de(fn(a.value.minDate)), ae = de(fn(a.value.maxDate));
      return gt(V, w) && pt(V, ae) || qe(V, w) || qe(V, ae);
    }
    if (a.value.minDate) {
      const w = de(fn(a.value.minDate));
      y = gt(V, w) || qe(V, w);
    }
    if (a.value.maxDate) {
      const w = de(fn(a.value.maxDate));
      y = pt(V, w) || qe(V, w);
    }
    return y;
  }, z = J(() => (j) => !e.enableTimePicker || e.ignoreTimeValidation ? true : $(j)), se = J(() => (j) => e.monthPicker ? Array.isArray(j) && (n.value.enabled || r.value.enabled) ? !j.filter((y) => !H(y)).length : H(j) : true);
  return { isDisabled: b, validateDate: s, validateMonthYearInRange: p, isDateRangeAllowed: h, checkMinMaxRange: x, isValidTime: $, isTimeValid: z, isMonthValid: se };
}, go = () => {
  const e = J(() => (a, r) => a == null ? void 0 : a.includes(r)), t = J(() => (a, r) => a.count ? a.solo ? true : r === 0 : true), n = J(() => (a, r) => a.count ? a.solo ? true : r === a.count - 1 : true);
  return { hideNavigationButtons: e, showLeftIcon: t, showRightIcon: n };
}, cy = (e, t, n) => {
  const a = te(0), r = un({ [Ia.timePicker]: !e.enableTimePicker || e.timePicker || e.monthPicker, [Ia.calendar]: false, [Ia.header]: false }), l = J(() => e.monthPicker || e.timePicker), o = (u) => {
    var p;
    if ((p = e.flow) != null && p.length) {
      if (!u && l.value) return d();
      r[u] = true, Object.keys(r).filter((v) => !r[v]).length || d();
    }
  }, i = () => {
    var u, p;
    (u = e.flow) != null && u.length && a.value !== -1 && (a.value += 1, t("flow-step", a.value), d()), ((p = e.flow) == null ? void 0 : p.length) === a.value && bt().then(() => s());
  }, s = () => {
    a.value = -1;
  }, c = (u, p, ...v) => {
    var b, h;
    e.flow[a.value] === u && n.value && ((h = (b = n.value)[p]) == null || h.call(b, ...v));
  }, d = (u = 0) => {
    u && (a.value += u), c(Nt.month, "toggleMonthPicker", true), c(Nt.year, "toggleYearPicker", true), c(Nt.calendar, "toggleTimePicker", false, true), c(Nt.time, "toggleTimePicker", true, true);
    const p = e.flow[a.value];
    (p === Nt.hours || p === Nt.minutes || p === Nt.seconds) && c(p, "toggleTimePicker", true, true, p);
  };
  return { childMount: o, updateFlowStep: i, resetFlow: s, handleFlow: d, flowStep: a };
}, dy = { key: 1, class: "dp__input_wrap" }, py = ["id", "name", "inputmode", "placeholder", "disabled", "readonly", "required", "value", "autocomplete", "aria-label", "aria-disabled", "aria-invalid"], fy = { key: 2, class: "dp__clear_icon" }, vy = /* @__PURE__ */ Rt({ compatConfig: { MODE: 3 }, __name: "DatepickerInput", props: { isMenuOpen: { type: Boolean, default: false }, inputValue: { type: String, default: "" }, ...vo }, emits: ["clear", "open", "update:input-value", "set-input-date", "close", "select-date", "set-empty-date", "toggle", "focus-prev", "focus", "blur", "real-blur"], setup(e, { expose: t, emit: n }) {
  const a = n, r = e, { defaultedTextInput: l, defaultedAriaLabels: o, defaultedInline: i, defaultedConfig: s, defaultedRange: c, defaultedMultiDates: d, defaultedUI: u, getDefaultPattern: p, getDefaultStartTime: v } = ct(r), { checkMinMaxRange: b } = Sa(r), h = te(), N = te(null), I = te(false), x = te(false), _ = J(() => ({ dp__pointer: !r.disabled && !r.readonly && !l.value.enabled, dp__disabled: r.disabled, dp__input_readonly: !l.value.enabled, dp__input: true, dp__input_icon_pad: !r.hideInputIcon, dp__input_valid: !!r.state, dp__input_invalid: r.state === false, dp__input_focus: I.value || r.isMenuOpen, dp__input_reg: !l.value.enabled, [r.inputClassName]: !!r.inputClassName, ...u.value.input ?? {} })), g = () => {
    a("set-input-date", null), r.clearable && r.autoApply && (a("set-empty-date"), h.value = null);
  }, R = (w) => {
    const ae = v();
    return Tg(w, l.value.format ?? p(), ae ?? wp({}, r.enableSeconds), r.inputValue, x.value, r.formatLocale);
  }, M = (w) => {
    const { rangeSeparator: ae } = l.value, [ce, ve] = w.split(`${ae}`);
    if (ce) {
      const _e = R(ce.trim()), q = ve ? R(ve.trim()) : null;
      if (dr(_e, q)) return;
      const oe = _e && q ? [_e, q] : [_e];
      b(q, oe, 0) && (h.value = _e ? oe : null);
    }
  }, C = () => {
    x.value = true;
  }, Y = (w) => {
    if (c.value.enabled) M(w);
    else if (d.value.enabled) {
      const ae = w.split(";");
      h.value = ae.map((ce) => R(ce.trim())).filter((ce) => ce);
    } else h.value = R(w);
  }, P = (w) => {
    var ae;
    const ce = typeof w == "string" ? w : (ae = w.target) == null ? void 0 : ae.value;
    ce !== "" ? (l.value.openMenu && !r.isMenuOpen && a("open"), Y(ce), a("set-input-date", h.value)) : g(), x.value = false, a("update:input-value", ce);
  }, $ = (w) => {
    l.value.enabled ? (Y(w.target.value), l.value.enterSubmit && Mi(h.value) && r.inputValue !== "" ? (a("set-input-date", h.value, true), h.value = null) : l.value.enterSubmit && r.inputValue === "" && (h.value = null, a("clear"))) : se(w);
  }, H = (w) => {
    l.value.enabled && l.value.tabSubmit && Y(w.target.value), l.value.tabSubmit && Mi(h.value) && r.inputValue !== "" ? (a("set-input-date", h.value, true, true), h.value = null) : l.value.tabSubmit && r.inputValue === "" && (h.value = null, a("clear", true));
  }, z = () => {
    I.value = true, a("focus"), bt().then(() => {
      var w;
      l.value.enabled && l.value.selectOnFocus && ((w = N.value) == null || w.select());
    });
  }, se = (w) => {
    w.preventDefault(), pa(w, s.value, true), l.value.enabled && l.value.openMenu && !i.value.input && !r.isMenuOpen ? a("open") : l.value.enabled || a("toggle");
  }, j = () => {
    a("real-blur"), I.value = false, (!r.isMenuOpen || i.value.enabled && i.value.input) && a("blur"), r.autoApply && l.value.enabled && h.value && !r.isMenuOpen && (a("set-input-date", h.value), a("select-date"), h.value = null);
  }, y = (w) => {
    pa(w, s.value, true), a("clear");
  }, V = (w) => {
    if (w.key === "Tab" && H(w), w.key === "Enter" && $(w), !l.value.enabled) {
      if (w.code === "Tab") return;
      w.preventDefault();
    }
  };
  return t({ focusInput: () => {
    var w;
    (w = N.value) == null || w.focus({ preventScroll: true });
  }, setParsedDate: (w) => {
    h.value = w;
  } }), (w, ae) => {
    var ce;
    return T(), F("div", { onClick: se }, [w.$slots.trigger && !w.$slots["dp-input"] && !f(i).enabled ? ye(w.$slots, "trigger", { key: 0 }) : Z("", true), !w.$slots.trigger && (!f(i).enabled || f(i).input) ? (T(), F("div", dy, [w.$slots["dp-input"] && !w.$slots.trigger && (!f(i).enabled || f(i).enabled && f(i).input) ? ye(w.$slots, "dp-input", { key: 0, value: e.inputValue, isMenuOpen: e.isMenuOpen, onInput: P, onEnter: $, onTab: H, onClear: y, onBlur: j, onKeypress: V, onPaste: C, onFocus: z, openMenu: () => w.$emit("open"), closeMenu: () => w.$emit("close"), toggleMenu: () => w.$emit("toggle") }) : Z("", true), w.$slots["dp-input"] ? Z("", true) : (T(), F("input", { key: 1, id: w.uid ? `dp-input-${w.uid}` : void 0, ref_key: "inputRef", ref: N, "data-test": "dp-input", name: w.name, class: pe(_.value), inputmode: f(l).enabled ? "text" : "none", placeholder: w.placeholder, disabled: w.disabled, readonly: w.readonly, required: w.required, value: e.inputValue, autocomplete: w.autocomplete, "aria-label": (ce = f(o)) == null ? void 0 : ce.input, "aria-disabled": w.disabled || void 0, "aria-invalid": w.state === false ? true : void 0, onInput: P, onBlur: j, onFocus: z, onKeypress: V, onKeydown: V, onPaste: C }, null, 42, py)), L("div", { onClick: ae[2] || (ae[2] = (ve) => a("toggle")) }, [w.$slots["input-icon"] && !w.hideInputIcon ? (T(), F("span", { key: 0, class: "dp__input_icon", onClick: ae[0] || (ae[0] = (ve) => a("toggle")) }, [ye(w.$slots, "input-icon")])) : Z("", true), !w.$slots["input-icon"] && !w.hideInputIcon && !w.$slots["dp-input"] ? (T(), Pe(f(xr), { key: 1, class: "dp__input_icon dp__input_icons", onClick: ae[1] || (ae[1] = (ve) => a("toggle")) })) : Z("", true)]), w.$slots["clear-icon"] && e.inputValue && w.clearable && !w.disabled && !w.readonly ? (T(), F("span", fy, [ye(w.$slots, "clear-icon", { clear: y })])) : Z("", true), w.clearable && !w.$slots["clear-icon"] && e.inputValue && !w.disabled && !w.readonly ? (T(), Pe(f(cp), { key: 3, class: "dp__clear_icon dp__input_icons", "data-test": "clear-icon", onClick: ae[3] || (ae[3] = da((ve) => y(ve), ["prevent"])) })) : Z("", true)])) : Z("", true)]);
  };
} }), my = typeof window < "u" ? window : void 0, Jo = () => {
}, hy = (e) => Ei() ? (wc(e), true) : false, gy = (e, t, n, a) => {
  if (!e) return Jo;
  let r = Jo;
  const l = He(() => f(e), (i) => {
    r(), i && (i.addEventListener(t, n, a), r = () => {
      i.removeEventListener(t, n, a), r = Jo;
    });
  }, { immediate: true, flush: "post" }), o = () => {
    l(), r();
  };
  return hy(o), o;
}, wy = (e, t, n, a = {}) => {
  const { window: r = my, event: l = "pointerdown" } = a;
  return r ? gy(r, l, (o) => {
    const i = kt(e), s = kt(t);
    !i || !s || i === o.target || o.composedPath().includes(i) || o.composedPath().includes(s) || n(o);
  }, { passive: true }) : void 0;
}, yy = /* @__PURE__ */ Rt({ compatConfig: { MODE: 3 }, __name: "VueDatePicker", props: { ...vo }, emits: ["update:model-value", "update:model-timezone-value", "text-submit", "closed", "cleared", "open", "focus", "blur", "internal-model-change", "recalculate-position", "flow-step", "update-month-year", "invalid-select", "invalid-fixed-range", "tooltip-open", "tooltip-close", "time-picker-open", "time-picker-close", "am-pm-change", "range-start", "range-end", "date-update", "invalid-date", "overlay-toggle"], setup(e, { expose: t, emit: n }) {
  const a = n, r = e, l = za(), o = te(false), i = ir(r, "modelValue"), s = ir(r, "timezone"), c = te(null), d = te(null), u = te(null), p = te(false), v = te(null), b = te(false), h = te(false), N = te(false), I = te(false), { setMenuFocused: x, setShiftKey: _ } = kp(), { clearArrowNav: g } = ka(), { validateDate: R, isValidTime: M } = Sa(r), { defaultedTransitions: C, defaultedTextInput: Y, defaultedInline: P, defaultedConfig: $, defaultedRange: H, defaultedMultiDates: z } = ct(r), { menuTransition: se, showTransition: j } = cl(C);
  ot(() => {
    W(r.modelValue), bt().then(() => {
      if (!P.value.enabled) {
        const ne = _e(v.value);
        ne == null || ne.addEventListener("scroll", E), window == null || window.addEventListener("resize", B);
      }
    }), P.value.enabled && (o.value = true), window == null || window.addEventListener("keyup", X), window == null || window.addEventListener("keydown", A);
  }), br(() => {
    if (!P.value.enabled) {
      const ne = _e(v.value);
      ne == null || ne.removeEventListener("scroll", E), window == null || window.removeEventListener("resize", B);
    }
    window == null || window.removeEventListener("keyup", X), window == null || window.removeEventListener("keydown", A);
  });
  const y = tn(l, "all", r.presetDates), V = tn(l, "input");
  He([i, s], () => {
    W(i.value);
  }, { deep: true });
  const { openOnTop: w, menuStyle: ae, xCorrect: ce, setMenuPosition: ve, getScrollableParent: _e, shadowRender: q } = oy({ menuRef: c, menuRefInner: d, inputRef: u, pickerWrapperRef: v, inline: P, emit: a, props: r, slots: l }), { inputValue: oe, internalModelValue: S, parseExternalModelValue: W, emitModelValue: G, formatInputValue: D, checkBeforeEmit: fe } = Qg(a, r, p), Ae = J(() => ({ dp__main: true, dp__theme_dark: r.dark, dp__theme_light: !r.dark, dp__flex_display: P.value.enabled, "dp--flex-display-collapsed": N.value, dp__flex_display_with_input: P.value.input })), re = J(() => r.dark ? "dp__theme_dark" : "dp__theme_light"), Oe = J(() => r.teleport ? { to: typeof r.teleport == "boolean" ? "body" : r.teleport, disabled: !r.teleport || P.value.enabled } : {}), O = J(() => ({ class: "dp__outer_menu_wrap" })), m = J(() => P.value.enabled && (r.timePicker || r.monthPicker || r.yearPicker || r.quarterPicker)), k = () => {
    var ne, me;
    return (me = (ne = u.value) == null ? void 0 : ne.$el) == null ? void 0 : me.getBoundingClientRect();
  }, E = () => {
    o.value && ($.value.closeOnScroll ? Le() : ve());
  }, B = () => {
    var ne;
    o.value && ve();
    const me = (ne = d.value) == null ? void 0 : ne.$el.getBoundingClientRect().width;
    N.value = document.body.offsetWidth <= me;
  }, X = (ne) => {
    ne.key === "Tab" && !P.value.enabled && !r.teleport && $.value.tabOutClosesMenu && (v.value.contains(document.activeElement) || Le()), h.value = ne.shiftKey;
  }, A = (ne) => {
    h.value = ne.shiftKey;
  }, U = () => {
    !r.disabled && !r.readonly && (q(Uu, r), ve(false), o.value = true, o.value && a("open"), o.value || De(), W(r.modelValue));
  }, ee = () => {
    var ne;
    oe.value = "", De(), (ne = u.value) == null || ne.setParsedDate(null), a("update:model-value", null), a("update:model-timezone-value", null), a("cleared"), $.value.closeOnClearValue && Le();
  }, le = () => {
    const ne = S.value;
    return !ne || !Array.isArray(ne) && R(ne) ? true : Array.isArray(ne) ? z.value.enabled || ne.length === 2 && R(ne[0]) && R(ne[1]) ? true : H.value.partialRange && !r.timePicker ? R(ne[0]) : false : false;
  }, ue = () => {
    fe() && le() ? (G(), Le()) : a("invalid-select", S.value);
  }, ie = (ne) => {
    be(), G(), $.value.closeOnAutoApply && !ne && Le();
  }, be = () => {
    u.value && Y.value.enabled && u.value.setParsedDate(S.value);
  }, xe = (ne = false) => {
    r.autoApply && M(S.value) && le() && (H.value.enabled && Array.isArray(S.value) ? (H.value.partialRange || S.value.length === 2) && ie(ne) : ie(ne));
  }, De = () => {
    Y.value.enabled || (S.value = null);
  }, Le = () => {
    P.value.enabled || (o.value && (o.value = false, ce.value = false, x(false), _(false), g(), a("closed"), oe.value && W(i.value)), De(), a("blur"));
  }, je = (ne, me, Se = false) => {
    if (!ne) {
      S.value = null;
      return;
    }
    const Mt = Array.isArray(ne) ? !ne.some((xn) => !R(xn)) : R(ne), Ut = M(ne);
    Mt && Ut && (I.value = true, S.value = ne, me && (b.value = Se, ue(), a("text-submit")), bt().then(() => {
      I.value = false;
    }));
  }, ze = () => {
    r.autoApply && M(S.value) && G(), be();
  }, dt = () => o.value ? Le() : U(), lt = (ne) => {
    S.value = ne;
  }, $t = () => {
    Y.value.enabled && (p.value = true, D()), a("focus");
  }, xt = () => {
    if (Y.value.enabled && (p.value = false, W(r.modelValue), b.value)) {
      const ne = Sg(v.value, h.value);
      ne == null || ne.focus();
    }
    a("blur");
  }, cn = (ne) => {
    d.value && d.value.updateMonthYear(0, { month: Yu(ne.month), year: Yu(ne.year) });
  }, Jt = (ne) => {
    W(ne ?? r.modelValue);
  }, tt = (ne, me) => {
    var Se;
    (Se = d.value) == null || Se.switchView(ne, me);
  }, Q = (ne) => $.value.onClickOutside ? $.value.onClickOutside(ne) : Le(), he = (ne = 0) => {
    var me;
    (me = d.value) == null || me.handleFlow(ne);
  };
  return wy(c, u, () => Q(le)), t({ closeMenu: Le, selectDate: ue, clearValue: ee, openMenu: U, onScroll: E, formatInputValue: D, updateInternalModelValue: lt, setMonthYear: cn, parseModel: Jt, switchView: tt, toggleMenu: dt, handleFlow: he }), (ne, me) => (T(), F("div", { ref_key: "pickerWrapperRef", ref: v, class: pe(Ae.value), "data-datepicker-instance": "" }, [Ne(vy, ft({ ref_key: "inputRef", ref: u, "input-value": f(oe), "onUpdate:inputValue": me[0] || (me[0] = (Se) => at(oe) ? oe.value = Se : null), "is-menu-open": o.value }, ne.$props, { onClear: ee, onOpen: U, onSetInputDate: je, onSetEmptyDate: f(G), onSelectDate: ue, onToggle: dt, onClose: Le, onFocus: $t, onBlur: xt, onRealBlur: me[1] || (me[1] = (Se) => p.value = false) }), jt({ _: 2 }, [Ve(f(V), (Se, Mt) => ({ name: Se, fn: Ie((Ut) => [ye(ne.$slots, Se, Ot(Zt(Ut)))]) }))]), 1040, ["input-value", "is-menu-open", "onSetEmptyDate"]), (T(), Pe(ol(ne.teleport ? qc : "div"), Ot(Zt(Oe.value)), { default: Ie(() => [Ne(_r, { name: f(se)(f(w)), css: f(j) && !f(P).enabled }, { default: Ie(() => [o.value ? (T(), F("div", ft({ key: 0, ref_key: "dpWrapMenuRef", ref: c }, O.value, { class: { "dp--menu-wrapper": !f(P).enabled }, style: f(P).enabled ? void 0 : f(ae) }), [Ne(Uu, ft({ ref_key: "dpMenuRef", ref: d }, ne.$props, { "internal-model-value": f(S), "onUpdate:internalModelValue": me[2] || (me[2] = (Se) => at(S) ? S.value = Se : null), class: { [re.value]: true, "dp--menu-wrapper": ne.teleport }, "open-on-top": f(w), "no-overlay-focus": m.value, collapse: N.value, "get-input-rect": k, "is-text-input-date": I.value, onClosePicker: Le, onSelectDate: ue, onAutoApply: xe, onTimeUpdate: ze, onFlowStep: me[3] || (me[3] = (Se) => ne.$emit("flow-step", Se)), onUpdateMonthYear: me[4] || (me[4] = (Se) => ne.$emit("update-month-year", Se)), onInvalidSelect: me[5] || (me[5] = (Se) => ne.$emit("invalid-select", f(S))), onAutoApplyInvalid: me[6] || (me[6] = (Se) => ne.$emit("invalid-select", Se)), onInvalidFixedRange: me[7] || (me[7] = (Se) => ne.$emit("invalid-fixed-range", Se)), onRecalculatePosition: f(ve), onTooltipOpen: me[8] || (me[8] = (Se) => ne.$emit("tooltip-open", Se)), onTooltipClose: me[9] || (me[9] = (Se) => ne.$emit("tooltip-close", Se)), onTimePickerOpen: me[10] || (me[10] = (Se) => ne.$emit("time-picker-open", Se)), onTimePickerClose: me[11] || (me[11] = (Se) => ne.$emit("time-picker-close", Se)), onAmPmChange: me[12] || (me[12] = (Se) => ne.$emit("am-pm-change", Se)), onRangeStart: me[13] || (me[13] = (Se) => ne.$emit("range-start", Se)), onRangeEnd: me[14] || (me[14] = (Se) => ne.$emit("range-end", Se)), onDateUpdate: me[15] || (me[15] = (Se) => ne.$emit("date-update", Se)), onInvalidDate: me[16] || (me[16] = (Se) => ne.$emit("invalid-date", Se)), onOverlayToggle: me[17] || (me[17] = (Se) => ne.$emit("overlay-toggle", Se)) }), jt({ _: 2 }, [Ve(f(y), (Se, Mt) => ({ name: Se, fn: Ie((Ut) => [ye(ne.$slots, Se, Ot(Zt({ ...Ut })))]) }))]), 1040, ["internal-model-value", "class", "open-on-top", "no-overlay-focus", "collapse", "is-text-input-date", "onRecalculatePosition"])], 16)) : Z("", true)]), _: 3 }, 8, ["name", "css"])]), _: 3 }, 16))], 2));
} }), wo = (() => {
  const e = yy;
  return e.install = (t) => {
    t.component("Vue3DatePicker", e);
  }, e;
})(), by = Object.freeze(Object.defineProperty({ __proto__: null, default: wo }, Symbol.toStringTag, { value: "Module" }));
Object.entries(by).forEach(([e, t]) => {
  e !== "default" && (wo[e] = t);
});
function Sn(e) {
  return e == null;
}
function _y(e, t, n) {
  const { object: a, valueProp: r, mode: l } = Pt(e), o = xa().proxy, i = n.iv, s = (u, p = true) => {
    i.value = d(u);
    const v = c(u);
    t.emit("change", v, o), p && (t.emit("input", v), t.emit("update:modelValue", v));
  }, c = (u) => a.value || Sn(u) ? u : Array.isArray(u) ? u.map((p) => p[r.value]) : u[r.value], d = (u) => Sn(u) ? l.value === "single" ? {} : [] : u;
  return { update: s };
}
function st(e) {
  return Qf(() => ({ get: e, set: () => {
  } }));
}
function xy(e, t) {
  const { value: n, modelValue: a, mode: r, valueProp: l } = Pt(e), o = te(r.value !== "single" ? [] : {}), i = st(() => a.value !== void 0 ? a.value : n.value), s = J(() => r.value === "single" ? o.value[l.value] : o.value.map((d) => d[l.value])), c = st(() => r.value !== "single" ? o.value.map((d) => d[l.value]).join(",") : o.value[l.value]);
  return { iv: o, internalValue: o, ev: i, externalValue: i, textValue: c, plainValue: s };
}
function ky(e, t, n) {
  const { regex: a } = Pt(e), r = xa().proxy, l = n.isOpen, o = n.open, i = te(null), s = () => {
    i.value = "";
  }, c = (p) => {
    i.value = p.target.value;
  }, d = (p) => {
    if (a.value) {
      let v = a.value;
      typeof v == "string" && (v = new RegExp(v)), p.key.match(v) || p.preventDefault();
    }
  }, u = (p) => {
    if (a.value) {
      let v = (p.clipboardData || window.clipboardData).getData("Text"), b = a.value;
      typeof b == "string" && (b = new RegExp(b)), v.split("").every((h) => !!h.match(b)) || p.preventDefault();
    }
    t.emit("paste", p, r);
  };
  return He(i, (p) => {
    !l.value && p && o(), t.emit("search-change", p, r);
  }), { search: i, clearSearch: s, handleSearchInput: c, handleKeypress: d, handlePaste: u };
}
function Sy(e, t, n) {
  const { groupSelect: a, mode: r, groups: l, disabledProp: o } = Pt(e), i = te(null), s = (c) => {
    c === void 0 || c !== null && c[o.value] || l.value && c && c.group && (r.value === "single" || !a.value) || (i.value = c);
  };
  return { pointer: i, setPointer: s, clearPointer: () => {
    s(null);
  } };
}
function ei(e, t = true) {
  return t ? String(e).toLowerCase().trim() : String(e).toLowerCase().normalize("NFD").trim().replace(/æ/g, "ae").replace(/œ/g, "oe").replace(/ø/g, "o").replace(new RegExp("\\p{Diacritic}", "gu"), "");
}
function Cy(e) {
  return Object.prototype.toString.call(e) === "[object Object]";
}
function My(e, t) {
  if (e.length !== t.length) return false;
  const n = t.slice().sort();
  return e.slice().sort().every(function(a, r) {
    return a === n[r];
  });
}
function Ty(e, t, n) {
  const { options: a, mode: r, trackBy: l, limit: o, hideSelected: i, createTag: s, createOption: c, label: d, appendNewTag: u, appendNewOption: p, multipleLabel: v, object: b, loading: h, delay: N, resolveOnLoad: I, minChars: x, filterResults: _, clearOnSearch: g, clearOnSelect: R, valueProp: M, allowAbsent: C, groupLabel: Y, canDeselect: P, max: $, strict: H, closeOnSelect: z, closeOnDeselect: se, groups: j, reverse: y, infinite: V, groupOptions: w, groupHideEmpty: ae, groupSelect: ce, onCreate: ve, disabledProp: _e, searchStart: q, searchFilter: oe } = Pt(e), S = xa().proxy, W = n.iv, G = n.ev, D = n.search, fe = n.clearSearch, Ae = n.update, re = n.pointer, Oe = n.setPointer, O = n.clearPointer, m = n.focus, k = n.deactivate, E = n.close, B = n.localize, X = te([]), A = te([]), U = te(false), ee = te(null), le = te(V.value && o.value === -1 ? 10 : o.value), ue = J({ get: () => A.value, set: (K) => A.value = K }), ie = st(() => s.value || c.value || false), be = st(() => u.value !== void 0 ? u.value : p.value !== void 0 ? p.value : true), xe = J(() => {
    if (j.value) {
      let K = je.value || [], we = [];
      return K.forEach((Ye) => {
        Mo(Ye[w.value]).forEach((mt) => {
          we.push(Object.assign({}, mt, Ye[_e.value] ? { [_e.value]: true } : {}));
        });
      }), we;
    } else {
      let K = Mo(A.value || []);
      return X.value.length && (K = K.concat(X.value)), K;
    }
  }), De = J(() => {
    let K = xe.value;
    return y.value && (K = K.reverse()), Jt.value.length && (K = Jt.value.concat(K)), Co(K);
  }), Le = J(() => {
    let K = De.value;
    return le.value > 0 && (K = K.slice(0, le.value)), K;
  }), je = J(() => {
    if (!j.value) return [];
    let K = [], we = A.value || [];
    return X.value.length && K.push({ [Y.value]: " ", [w.value]: [...X.value], __CREATE__: true }), K.concat(we);
  }), ze = J(() => {
    let K = [...je.value].map((we) => ({ ...we }));
    return Jt.value.length && (K[0] && K[0].__CREATE__ ? K[0][w.value] = [...Jt.value, ...K[0][w.value]] : K = [{ [Y.value]: " ", [w.value]: [...Jt.value], __CREATE__: true }].concat(K)), K;
  }), dt = J(() => {
    if (!j.value) return [];
    let K = ze.value;
    return uf((K || []).map((we, Ye) => {
      const mt = Mo(we[w.value]);
      return { ...we, index: Ye, group: true, [w.value]: Co(mt, false).map((Ma) => Object.assign({}, Ma, we[_e.value] ? { [_e.value]: true } : {})), __VISIBLE__: Co(mt).map((Ma) => Object.assign({}, Ma, we[_e.value] ? { [_e.value]: true } : {})) };
    }));
  }), lt = J(() => {
    switch (r.value) {
      case "single":
        return !Sn(W.value[M.value]);
      case "multiple":
      case "tags":
        return !Sn(W.value) && W.value.length > 0;
    }
  }), $t = J(() => v.value !== void 0 ? v.value(W.value, S) : W.value && W.value.length > 1 ? `${W.value.length} options selected` : "1 option selected"), xt = st(() => !xe.value.length && !U.value && !Jt.value.length), cn = st(() => xe.value.length > 0 && Le.value.length == 0 && (D.value && j.value || !j.value)), Jt = J(() => ie.value === false || !D.value ? [] : lf(D.value) !== -1 ? [] : [{ [M.value]: D.value, [tt.value[0]]: D.value, [d.value]: D.value, __CREATE__: true }]), tt = J(() => l.value ? Array.isArray(l.value) ? l.value : [l.value] : [d.value]), Q = st(() => {
    switch (r.value) {
      case "single":
        return null;
      case "multiple":
      case "tags":
        return [];
    }
  }), he = st(() => h.value || U.value), ne = (K) => {
    switch (typeof K != "object" && (K = En(K)), r.value) {
      case "single":
        Ae(K);
        break;
      case "multiple":
      case "tags":
        Ae(W.value.concat(K));
        break;
    }
    t.emit("select", Se(K), K, S);
  }, me = (K) => {
    switch (typeof K != "object" && (K = En(K)), r.value) {
      case "single":
        xn();
        break;
      case "tags":
      case "multiple":
        Ae(Array.isArray(K) ? W.value.filter((we) => K.map((Ye) => Ye[M.value]).indexOf(we[M.value]) === -1) : W.value.filter((we) => we[M.value] != K[M.value]));
        break;
    }
    t.emit("deselect", Se(K), K, S);
  }, Se = (K) => b.value ? K : K[M.value], Mt = (K) => {
    me(K);
  }, Ut = (K, we) => {
    if (we.button !== 0) {
      we.preventDefault();
      return;
    }
    Mt(K);
  }, xn = () => {
    Ae(Q.value), t.emit("clear", S);
  }, Gt = (K) => {
    if (K.group !== void 0) return r.value === "single" ? false : rf(K[w.value]) && K[w.value].length;
    switch (r.value) {
      case "single":
        return !Sn(W.value) && W.value[M.value] == K[M.value];
      case "tags":
      case "multiple":
        return !Sn(W.value) && W.value.map((we) => we[M.value]).indexOf(K[M.value]) !== -1;
    }
  }, Rn = (K) => K[_e.value] === true, an = () => $ === void 0 || $.value === -1 || !lt.value && $.value > 0 ? false : W.value.length >= $.value, kr = (K) => {
    if (!Rn(K)) {
      if (ve.value && !Gt(K) && K.__CREATE__ && (K = { ...K }, delete K.__CREATE__, K = ve.value(K, S), K instanceof Promise)) {
        U.value = true, K.then((we) => {
          U.value = false, fl(we);
        });
        return;
      }
      fl(K);
    }
  }, fl = (K) => {
    switch (K.__CREATE__ && (K = { ...K }, delete K.__CREATE__), r.value) {
      case "single":
        if (K && Gt(K)) {
          P.value && me(K), se.value && (O(), E());
          return;
        }
        K && So(K), R.value && fe(), z.value && (O(), E()), K && ne(K);
        break;
      case "multiple":
        if (K && Gt(K)) {
          me(K), se.value && (O(), E());
          return;
        }
        if (an()) {
          t.emit("max", S);
          return;
        }
        K && (So(K), ne(K)), R.value && fe(), i.value && O(), z.value && E();
        break;
      case "tags":
        if (K && Gt(K)) {
          me(K), se.value && (O(), E());
          return;
        }
        if (an()) {
          t.emit("max", S);
          return;
        }
        K && So(K), R.value && fe(), K && ne(K), i.value && O(), z.value && E();
        break;
    }
    z.value || m();
  }, tf = (K) => {
    if (!(Rn(K) || r.value === "single" || !ce.value)) {
      switch (r.value) {
        case "multiple":
        case "tags":
          af(K[w.value]) ? me(K[w.value]) : ne(K[w.value].filter((we) => W.value.map((Ye) => Ye[M.value]).indexOf(we[M.value]) === -1).filter((we) => !we[_e.value]).filter((we, Ye) => W.value.length + 1 + Ye <= $.value || $.value === -1)), i.value && re.value && Oe(dt.value.filter((we) => !we[_e.value])[re.value.index]);
          break;
      }
      z.value && k();
    }
  }, So = (K) => {
    En(K[M.value]) === void 0 && ie.value && (t.emit("tag", K[M.value], S), t.emit("option", K[M.value], S), t.emit("create", K[M.value], S), be.value && sf(K), fe());
  }, nf = () => {
    r.value !== "single" && ne(Le.value.filter((K) => !K.disabled && !Gt(K)));
  }, af = (K) => K.find((we) => !Gt(we) && !we[_e.value]) === void 0, rf = (K) => K.find((we) => !Gt(we)) === void 0, En = (K) => xe.value[xe.value.map((we) => String(we[M.value])).indexOf(String(K))], lf = (K) => xe.value.findIndex((we) => tt.value.some((Ye) => (parseInt(we[Ye]) == we[Ye] ? parseInt(we[Ye]) : we[Ye]) === (parseInt(K) == K ? parseInt(K) : K))), of = (K) => ["tags", "multiple"].indexOf(r.value) !== -1 && i.value && Gt(K), sf = (K) => {
    X.value.push(K);
  }, uf = (K) => ae.value ? K.filter((we) => D.value ? we.__VISIBLE__.length : we[w.value].length) : K.filter((we) => D.value ? we.__VISIBLE__.length : true), Co = (K, we = true) => {
    let Ye = K;
    if (D.value && _.value) {
      let mt = oe.value;
      mt || (mt = (Ma, Ls, M5) => tt.value.some((df) => {
        let Os = ei(B(Ma[df]), H.value);
        return q.value ? Os.startsWith(ei(Ls, H.value)) : Os.indexOf(ei(Ls, H.value)) !== -1;
      })), Ye = Ye.filter((Ma) => mt(Ma, D.value, S));
    }
    return i.value && we && (Ye = Ye.filter((mt) => !of(mt))), Ye;
  }, Mo = (K) => {
    let we = K;
    return Cy(we) && (we = Object.keys(we).map((Ye) => {
      let mt = we[Ye];
      return { [M.value]: Ye, [tt.value[0]]: mt, [d.value]: mt };
    })), we = we.map((Ye) => typeof Ye == "object" ? Ye : { [M.value]: Ye, [tt.value[0]]: Ye, [d.value]: Ye }), we;
  }, vl = () => {
    Sn(G.value) || (W.value = hl(G.value));
  }, ml = (K) => (U.value = true, new Promise((we, Ye) => {
    a.value(D.value, S).then((mt) => {
      A.value = mt || [], typeof K == "function" && K(mt), U.value = false;
    }).catch((mt) => {
      console.error(mt), A.value = [], U.value = false;
    }).finally(() => {
      we();
    });
  })), To = () => {
    if (lt.value) if (r.value === "single") {
      let K = En(W.value[M.value]);
      if (K !== void 0) {
        let we = K[d.value];
        W.value[d.value] = we, b.value && (G.value[d.value] = we);
      }
    } else W.value.forEach((K, we) => {
      let Ye = En(W.value[we][M.value]);
      if (Ye !== void 0) {
        let mt = Ye[d.value];
        W.value[we][d.value] = mt, b.value && (G.value[we][d.value] = mt);
      }
    });
  }, cf = (K) => {
    ml(K);
  }, hl = (K) => Sn(K) ? r.value === "single" ? {} : [] : b.value ? K : r.value === "single" ? En(K) || (C.value ? { [d.value]: K, [M.value]: K, [tt.value[0]]: K } : {}) : K.filter((we) => !!En(we) || C.value).map((we) => En(we) || { [d.value]: we, [M.value]: we, [tt.value[0]]: we }), Ds = () => {
    ee.value = He(D, (K) => {
      K.length < x.value || !K && x.value !== 0 || (U.value = true, g.value && (A.value = []), setTimeout(() => {
        K == D.value && a.value(D.value, S).then((we) => {
          (K == D.value || !D.value) && (A.value = we, re.value = Le.value.filter((Ye) => Ye[_e.value] !== true)[0] || null, U.value = false);
        }).catch((we) => {
          console.error(we);
        });
      }, N.value));
    }, { flush: "sync" });
  };
  if (r.value !== "single" && !Sn(G.value) && !Array.isArray(G.value)) throw new Error(`v-model must be an array when using "${r.value}" mode`);
  return a && typeof a.value == "function" ? I.value ? ml(vl) : b.value == true && vl() : (A.value = a.value, vl()), N.value > -1 && Ds(), He(N, (K, we) => {
    ee.value && ee.value(), K >= 0 && Ds();
  }), He(G, (K) => {
    if (Sn(K)) {
      Ae(hl(K), false);
      return;
    }
    switch (r.value) {
      case "single":
        (b.value ? K[M.value] != W.value[M.value] : K != W.value[M.value]) && Ae(hl(K), false);
        break;
      case "multiple":
      case "tags":
        My(b.value ? K.map((we) => we[M.value]) : K, W.value.map((we) => we[M.value])) || Ae(hl(K), false);
        break;
    }
  }, { deep: true }), He(a, (K, we) => {
    typeof e.options == "function" ? I.value && (!we || K && K.toString() !== we.toString()) && ml() : (A.value = e.options, Object.keys(W.value).length || vl(), To());
  }), He(d, To), He(o, (K, we) => {
    le.value = V.value && K === -1 ? 10 : K;
  }), { resolvedOptions: ue, pfo: De, fo: Le, filteredOptions: Le, hasSelected: lt, multipleLabelText: $t, eo: xe, extendedOptions: xe, eg: je, extendedGroups: je, fg: dt, filteredGroups: dt, noOptions: xt, noResults: cn, resolving: U, busy: he, offset: le, select: ne, deselect: me, remove: Mt, selectAll: nf, clear: xn, isSelected: Gt, isDisabled: Rn, isMax: an, getOption: En, handleOptionClick: kr, handleGroupClick: tf, handleTagRemove: Ut, refreshOptions: cf, resolveOptions: ml, refreshLabels: To };
}
function Ay(e, t, n) {
  const { valueProp: a, showOptions: r, searchable: l, groupLabel: o, groups: i, mode: s, groupSelect: c, disabledProp: d, groupOptions: u } = Pt(e), p = n.fo, v = n.fg, b = n.handleOptionClick, h = n.handleGroupClick, N = n.search, I = n.pointer, x = n.setPointer, _ = n.clearPointer, g = n.multiselect, R = n.isOpen, M = J(() => p.value.filter((G) => !G[d.value])), C = J(() => v.value.filter((G) => !G[d.value])), Y = st(() => s.value !== "single" && c.value), P = st(() => I.value && I.value.group), $ = J(() => S(I.value)), H = J(() => {
    const G = P.value ? I.value : S(I.value), D = C.value.map((Ae) => Ae[o.value]).indexOf(G[o.value]);
    let fe = C.value[D - 1];
    return fe === void 0 && (fe = se.value), fe;
  }), z = J(() => {
    let G = C.value.map((D) => D.label).indexOf(P.value ? I.value[o.value] : S(I.value)[o.value]) + 1;
    return C.value.length <= G && (G = 0), C.value[G];
  }), se = J(() => [...C.value].slice(-1)[0]), j = J(() => I.value.__VISIBLE__.filter((G) => !G[d.value])[0]), y = J(() => {
    const G = $.value.__VISIBLE__.filter((D) => !D[d.value]);
    return G[G.map((D) => D[a.value]).indexOf(I.value[a.value]) - 1];
  }), V = J(() => {
    const G = S(I.value).__VISIBLE__.filter((D) => !D[d.value]);
    return G[G.map((D) => D[a.value]).indexOf(I.value[a.value]) + 1];
  }), w = J(() => [...H.value.__VISIBLE__.filter((G) => !G[d.value])].slice(-1)[0]), ae = J(() => [...se.value.__VISIBLE__.filter((G) => !G[d.value])].slice(-1)[0]), ce = (G) => I.value && (!G.group && I.value[a.value] === G[a.value] || G.group !== void 0 && I.value[o.value] === G[o.value]) ? true : void 0, ve = () => {
    x(M.value[0] || null);
  }, _e = () => {
    !I.value || I.value[d.value] === true || (P.value ? h(I.value) : b(I.value));
  }, q = () => {
    if (I.value === null) x((i.value && Y.value ? C.value[0].__CREATE__ ? M.value[0] : C.value[0] : M.value[0]) || null);
    else if (i.value && Y.value) {
      let G = P.value ? j.value : V.value;
      G === void 0 && (G = z.value, G.__CREATE__ && (G = G[u.value][0])), x(G || null);
    } else {
      let G = M.value.map((D) => D[a.value]).indexOf(I.value[a.value]) + 1;
      M.value.length <= G && (G = 0), x(M.value[G] || null);
    }
    bt(() => {
      W();
    });
  }, oe = () => {
    if (I.value === null) {
      let G = M.value[M.value.length - 1];
      i.value && Y.value && (G = ae.value, G === void 0 && (G = se.value)), x(G || null);
    } else if (i.value && Y.value) {
      let G = P.value ? w.value : y.value;
      G === void 0 && (G = P.value ? H.value : $.value, G.__CREATE__ && (G = w.value, G === void 0 && (G = H.value))), x(G || null);
    } else {
      let G = M.value.map((D) => D[a.value]).indexOf(I.value[a.value]) - 1;
      G < 0 && (G = M.value.length - 1), x(M.value[G] || null);
    }
    bt(() => {
      W();
    });
  }, S = (G) => C.value.find((D) => D.__VISIBLE__.map((fe) => fe[a.value]).indexOf(G[a.value]) !== -1), W = () => {
    let G = g.value.querySelector("[data-pointed]");
    if (!G) return;
    let D = G.parentElement.parentElement;
    i.value && (D = P.value ? G.parentElement.parentElement.parentElement : G.parentElement.parentElement.parentElement.parentElement), G.offsetTop + G.offsetHeight > D.clientHeight + D.scrollTop && (D.scrollTop = G.offsetTop + G.offsetHeight - D.clientHeight), G.offsetTop < D.scrollTop && (D.scrollTop = G.offsetTop);
  };
  return He(N, (G) => {
    l.value && (G.length && r.value ? ve() : _());
  }), He(R, (G) => {
    if (G && g && g.value) {
      let D = g.value.querySelectorAll("[data-selected]")[0];
      if (!D) return;
      let fe = D.parentElement.parentElement;
      bt(() => {
        fe.scrollTop = D.offsetTop;
      });
    }
  }), { pointer: I, canPointGroups: Y, isPointed: ce, setPointerFirst: ve, selectPointer: _e, forwardPointer: q, backwardPointer: oe };
}
function nn(e) {
  if (e == null) return window;
  if (e.toString() !== "[object Window]") {
    var t = e.ownerDocument;
    return t && t.defaultView || window;
  }
  return e;
}
function qa(e) {
  var t = nn(e).Element;
  return e instanceof t || e instanceof Element;
}
function sn(e) {
  var t = nn(e).HTMLElement;
  return e instanceof t || e instanceof HTMLElement;
}
function ws(e) {
  if (typeof ShadowRoot > "u") return false;
  var t = nn(e).ShadowRoot;
  return e instanceof t || e instanceof ShadowRoot;
}
var ja = Math.max, Kl = Math.min, mr = Math.round;
function Ti() {
  var e = navigator.userAgentData;
  return e != null && e.brands && Array.isArray(e.brands) ? e.brands.map(function(t) {
    return t.brand + "/" + t.version;
  }).join(" ") : navigator.userAgent;
}
function Lp() {
  return !/^((?!chrome|android).)*safari/i.test(Ti());
}
function hr(e, t, n) {
  t === void 0 && (t = false), n === void 0 && (n = false);
  var a = e.getBoundingClientRect(), r = 1, l = 1;
  t && sn(e) && (r = e.offsetWidth > 0 && mr(a.width) / e.offsetWidth || 1, l = e.offsetHeight > 0 && mr(a.height) / e.offsetHeight || 1);
  var o = qa(e) ? nn(e) : window, i = o.visualViewport, s = !Lp() && n, c = (a.left + (s && i ? i.offsetLeft : 0)) / r, d = (a.top + (s && i ? i.offsetTop : 0)) / l, u = a.width / r, p = a.height / l;
  return { width: u, height: p, top: d, right: c + u, bottom: d + p, left: c, x: c, y: d };
}
function ys(e) {
  var t = nn(e), n = t.pageXOffset, a = t.pageYOffset;
  return { scrollLeft: n, scrollTop: a };
}
function Dy(e) {
  return { scrollLeft: e.scrollLeft, scrollTop: e.scrollTop };
}
function Ly(e) {
  return e === nn(e) || !sn(e) ? ys(e) : Dy(e);
}
function Pn(e) {
  return e ? (e.nodeName || "").toLowerCase() : null;
}
function Ca(e) {
  return ((qa(e) ? e.ownerDocument : e.document) || window.document).documentElement;
}
function bs(e) {
  return hr(Ca(e)).left + ys(e).scrollLeft;
}
function Un(e) {
  return nn(e).getComputedStyle(e);
}
function _s(e) {
  var t = Un(e), n = t.overflow, a = t.overflowX, r = t.overflowY;
  return /auto|scroll|overlay|hidden/.test(n + r + a);
}
function Oy(e) {
  var t = e.getBoundingClientRect(), n = mr(t.width) / e.offsetWidth || 1, a = mr(t.height) / e.offsetHeight || 1;
  return n !== 1 || a !== 1;
}
function Py(e, t, n) {
  n === void 0 && (n = false);
  var a = sn(t), r = sn(t) && Oy(t), l = Ca(t), o = hr(e, r, n), i = { scrollLeft: 0, scrollTop: 0 }, s = { x: 0, y: 0 };
  return (a || !a && !n) && ((Pn(t) !== "body" || _s(l)) && (i = Ly(t)), sn(t) ? (s = hr(t, true), s.x += t.clientLeft, s.y += t.clientTop) : l && (s.x = bs(l))), { x: o.left + i.scrollLeft - s.x, y: o.top + i.scrollTop - s.y, width: o.width, height: o.height };
}
function Op(e) {
  var t = hr(e), n = e.offsetWidth, a = e.offsetHeight;
  return Math.abs(t.width - n) <= 1 && (n = t.width), Math.abs(t.height - a) <= 1 && (a = t.height), { x: e.offsetLeft, y: e.offsetTop, width: n, height: a };
}
function yo(e) {
  return Pn(e) === "html" ? e : e.assignedSlot || e.parentNode || (ws(e) ? e.host : null) || Ca(e);
}
function Pp(e) {
  return ["html", "body", "#document"].indexOf(Pn(e)) >= 0 ? e.ownerDocument.body : sn(e) && _s(e) ? e : Pp(yo(e));
}
function zr(e, t) {
  var n;
  t === void 0 && (t = []);
  var a = Pp(e), r = a === ((n = e.ownerDocument) == null ? void 0 : n.body), l = nn(a), o = r ? [l].concat(l.visualViewport || [], _s(a) ? a : []) : a, i = t.concat(o);
  return r ? i : i.concat(zr(yo(o)));
}
function $y(e) {
  return ["table", "td", "th"].indexOf(Pn(e)) >= 0;
}
function Gu(e) {
  return !sn(e) || Un(e).position === "fixed" ? null : e.offsetParent;
}
function Ry(e) {
  var t = /firefox/i.test(Ti()), n = /Trident/i.test(Ti());
  if (n && sn(e)) {
    var a = Un(e);
    if (a.position === "fixed") return null;
  }
  var r = yo(e);
  for (ws(r) && (r = r.host); sn(r) && ["html", "body"].indexOf(Pn(r)) < 0; ) {
    var l = Un(r);
    if (l.transform !== "none" || l.perspective !== "none" || l.contain === "paint" || ["transform", "perspective"].indexOf(l.willChange) !== -1 || t && l.willChange === "filter" || t && l.filter && l.filter !== "none") return r;
    r = r.parentNode;
  }
  return null;
}
function bo(e) {
  for (var t = nn(e), n = Gu(e); n && $y(n) && Un(n).position === "static"; ) n = Gu(n);
  return n && (Pn(n) === "html" || Pn(n) === "body" && Un(n).position === "static") ? t : n || Ry(e) || t;
}
var gn = "top", $n = "bottom", ga = "right", Kn = "left", xs = "auto", _o = [gn, $n, ga, Kn], gr = "start", nl = "end", Ey = "clippingParents", $p = "viewport", Lr = "popper", Ny = "reference", Qu = _o.reduce(function(e, t) {
  return e.concat([t + "-" + gr, t + "-" + nl]);
}, []), Iy = [].concat(_o, [xs]).reduce(function(e, t) {
  return e.concat([t, t + "-" + gr, t + "-" + nl]);
}, []), Vy = "beforeRead", jy = "read", By = "afterRead", Fy = "beforeMain", Yy = "main", qy = "afterMain", zy = "beforeWrite", Hy = "write", Ky = "afterWrite", Zy = [Vy, jy, By, Fy, Yy, qy, zy, Hy, Ky];
function Wy(e) {
  var t = /* @__PURE__ */ new Map(), n = /* @__PURE__ */ new Set(), a = [];
  e.forEach(function(l) {
    t.set(l.name, l);
  });
  function r(l) {
    n.add(l.name);
    var o = [].concat(l.requires || [], l.requiresIfExists || []);
    o.forEach(function(i) {
      if (!n.has(i)) {
        var s = t.get(i);
        s && r(s);
      }
    }), a.push(l);
  }
  return e.forEach(function(l) {
    n.has(l.name) || r(l);
  }), a;
}
function Uy(e) {
  var t = Wy(e);
  return Zy.reduce(function(n, a) {
    return n.concat(t.filter(function(r) {
      return r.phase === a;
    }));
  }, []);
}
function Gy(e) {
  var t;
  return function() {
    return t || (t = new Promise(function(n) {
      Promise.resolve().then(function() {
        t = void 0, n(e());
      });
    })), t;
  };
}
function Qy(e) {
  var t = e.reduce(function(n, a) {
    var r = n[a.name];
    return n[a.name] = r ? Object.assign({}, r, a, { options: Object.assign({}, r.options, a.options), data: Object.assign({}, r.data, a.data) }) : a, n;
  }, {});
  return Object.keys(t).map(function(n) {
    return t[n];
  });
}
function Xy(e, t) {
  var n = nn(e), a = Ca(e), r = n.visualViewport, l = a.clientWidth, o = a.clientHeight, i = 0, s = 0;
  if (r) {
    l = r.width, o = r.height;
    var c = Lp();
    (c || !c && t === "fixed") && (i = r.offsetLeft, s = r.offsetTop);
  }
  return { width: l, height: o, x: i + bs(e), y: s };
}
function Jy(e) {
  var t, n = Ca(e), a = ys(e), r = (t = e.ownerDocument) == null ? void 0 : t.body, l = ja(n.scrollWidth, n.clientWidth, r ? r.scrollWidth : 0, r ? r.clientWidth : 0), o = ja(n.scrollHeight, n.clientHeight, r ? r.scrollHeight : 0, r ? r.clientHeight : 0), i = -a.scrollLeft + bs(e), s = -a.scrollTop;
  return Un(r || n).direction === "rtl" && (i += ja(n.clientWidth, r ? r.clientWidth : 0) - l), { width: l, height: o, x: i, y: s };
}
function eb(e, t) {
  var n = t.getRootNode && t.getRootNode();
  if (e.contains(t)) return true;
  if (n && ws(n)) {
    var a = t;
    do {
      if (a && e.isSameNode(a)) return true;
      a = a.parentNode || a.host;
    } while (a);
  }
  return false;
}
function Ai(e) {
  return Object.assign({}, e, { left: e.x, top: e.y, right: e.x + e.width, bottom: e.y + e.height });
}
function tb(e, t) {
  var n = hr(e, false, t === "fixed");
  return n.top = n.top + e.clientTop, n.left = n.left + e.clientLeft, n.bottom = n.top + e.clientHeight, n.right = n.left + e.clientWidth, n.width = e.clientWidth, n.height = e.clientHeight, n.x = n.left, n.y = n.top, n;
}
function Xu(e, t, n) {
  return t === $p ? Ai(Xy(e, n)) : qa(t) ? tb(t, n) : Ai(Jy(Ca(e)));
}
function nb(e) {
  var t = zr(yo(e)), n = ["absolute", "fixed"].indexOf(Un(e).position) >= 0, a = n && sn(e) ? bo(e) : e;
  return qa(a) ? t.filter(function(r) {
    return qa(r) && eb(r, a) && Pn(r) !== "body";
  }) : [];
}
function ab(e, t, n, a) {
  var r = t === "clippingParents" ? nb(e) : [].concat(t), l = [].concat(r, [n]), o = l[0], i = l.reduce(function(s, c) {
    var d = Xu(e, c, a);
    return s.top = ja(d.top, s.top), s.right = Kl(d.right, s.right), s.bottom = Kl(d.bottom, s.bottom), s.left = ja(d.left, s.left), s;
  }, Xu(e, o, a));
  return i.width = i.right - i.left, i.height = i.bottom - i.top, i.x = i.left, i.y = i.top, i;
}
function va(e) {
  return e.split("-")[0];
}
function wr(e) {
  return e.split("-")[1];
}
function Rp(e) {
  return ["top", "bottom"].indexOf(e) >= 0 ? "x" : "y";
}
function Ep(e) {
  var t = e.reference, n = e.element, a = e.placement, r = a ? va(a) : null, l = a ? wr(a) : null, o = t.x + t.width / 2 - n.width / 2, i = t.y + t.height / 2 - n.height / 2, s;
  switch (r) {
    case gn:
      s = { x: o, y: t.y - n.height };
      break;
    case $n:
      s = { x: o, y: t.y + t.height };
      break;
    case ga:
      s = { x: t.x + t.width, y: i };
      break;
    case Kn:
      s = { x: t.x - n.width, y: i };
      break;
    default:
      s = { x: t.x, y: t.y };
  }
  var c = r ? Rp(r) : null;
  if (c != null) {
    var d = c === "y" ? "height" : "width";
    switch (l) {
      case gr:
        s[c] = s[c] - (t[d] / 2 - n[d] / 2);
        break;
      case nl:
        s[c] = s[c] + (t[d] / 2 - n[d] / 2);
        break;
    }
  }
  return s;
}
function Np() {
  return { top: 0, right: 0, bottom: 0, left: 0 };
}
function rb(e) {
  return Object.assign({}, Np(), e);
}
function lb(e, t) {
  return t.reduce(function(n, a) {
    return n[a] = e, n;
  }, {});
}
function ks(e, t) {
  t === void 0 && (t = {});
  var n = t, a = n.placement, r = a === void 0 ? e.placement : a, l = n.strategy, o = l === void 0 ? e.strategy : l, i = n.boundary, s = i === void 0 ? Ey : i, c = n.rootBoundary, d = c === void 0 ? $p : c, u = n.elementContext, p = u === void 0 ? Lr : u, v = n.altBoundary, b = v === void 0 ? false : v, h = n.padding, N = h === void 0 ? 0 : h, I = rb(typeof N != "number" ? N : lb(N, _o)), x = p === Lr ? Ny : Lr, _ = e.rects.popper, g = e.elements[b ? x : p], R = ab(qa(g) ? g : g.contextElement || Ca(e.elements.popper), s, d, o), M = hr(e.elements.reference), C = Ep({ reference: M, element: _, strategy: "absolute", placement: r }), Y = Ai(Object.assign({}, _, C)), P = p === Lr ? Y : M, $ = { top: R.top - P.top + I.top, bottom: P.bottom - R.bottom + I.bottom, left: R.left - P.left + I.left, right: P.right - R.right + I.right }, H = e.modifiersData.offset;
  if (p === Lr && H) {
    var z = H[r];
    Object.keys($).forEach(function(se) {
      var j = [ga, $n].indexOf(se) >= 0 ? 1 : -1, y = [gn, $n].indexOf(se) >= 0 ? "y" : "x";
      $[se] += z[y] * j;
    });
  }
  return $;
}
var Ju = { placement: "bottom", modifiers: [], strategy: "absolute" };
function ec() {
  for (var e = arguments.length, t = new Array(e), n = 0; n < e; n++) t[n] = arguments[n];
  return !t.some(function(a) {
    return !(a && typeof a.getBoundingClientRect == "function");
  });
}
function ob(e) {
  e === void 0 && (e = {});
  var t = e, n = t.defaultModifiers, a = n === void 0 ? [] : n, r = t.defaultOptions, l = r === void 0 ? Ju : r;
  return function(o, i, s) {
    s === void 0 && (s = l);
    var c = { placement: "bottom", orderedModifiers: [], options: Object.assign({}, Ju, l), modifiersData: {}, elements: { reference: o, popper: i }, attributes: {}, styles: {} }, d = [], u = false, p = { state: c, setOptions: function(h) {
      var N = typeof h == "function" ? h(c.options) : h;
      b(), c.options = Object.assign({}, l, c.options, N), c.scrollParents = { reference: qa(o) ? zr(o) : o.contextElement ? zr(o.contextElement) : [], popper: zr(i) };
      var I = Uy(Qy([].concat(a, c.options.modifiers)));
      return c.orderedModifiers = I.filter(function(x) {
        return x.enabled;
      }), v(), p.update();
    }, forceUpdate: function() {
      if (!u) {
        var h = c.elements, N = h.reference, I = h.popper;
        if (ec(N, I)) {
          c.rects = { reference: Py(N, bo(I), c.options.strategy === "fixed"), popper: Op(I) }, c.reset = false, c.placement = c.options.placement, c.orderedModifiers.forEach(function(Y) {
            return c.modifiersData[Y.name] = Object.assign({}, Y.data);
          });
          for (var x = 0; x < c.orderedModifiers.length; x++) {
            if (c.reset === true) {
              c.reset = false, x = -1;
              continue;
            }
            var _ = c.orderedModifiers[x], g = _.fn, R = _.options, M = R === void 0 ? {} : R, C = _.name;
            typeof g == "function" && (c = g({ state: c, options: M, name: C, instance: p }) || c);
          }
        }
      }
    }, update: Gy(function() {
      return new Promise(function(h) {
        p.forceUpdate(), h(c);
      });
    }), destroy: function() {
      b(), u = true;
    } };
    if (!ec(o, i)) return p;
    p.setOptions(s).then(function(h) {
      !u && s.onFirstUpdate && s.onFirstUpdate(h);
    });
    function v() {
      c.orderedModifiers.forEach(function(h) {
        var N = h.name, I = h.options, x = I === void 0 ? {} : I, _ = h.effect;
        if (typeof _ == "function") {
          var g = _({ state: c, name: N, instance: p, options: x }), R = function() {
          };
          d.push(g || R);
        }
      });
    }
    function b() {
      d.forEach(function(h) {
        return h();
      }), d = [];
    }
    return p;
  };
}
var Dl = { passive: true };
function ib(e) {
  var t = e.state, n = e.instance, a = e.options, r = a.scroll, l = r === void 0 ? true : r, o = a.resize, i = o === void 0 ? true : o, s = nn(t.elements.popper), c = [].concat(t.scrollParents.reference, t.scrollParents.popper);
  return l && c.forEach(function(d) {
    d.addEventListener("scroll", n.update, Dl);
  }), i && s.addEventListener("resize", n.update, Dl), function() {
    l && c.forEach(function(d) {
      d.removeEventListener("scroll", n.update, Dl);
    }), i && s.removeEventListener("resize", n.update, Dl);
  };
}
var sb = { name: "eventListeners", enabled: true, phase: "write", fn: function() {
}, effect: ib, data: {} };
function ub(e) {
  var t = e.state, n = e.name;
  t.modifiersData[n] = Ep({ reference: t.rects.reference, element: t.rects.popper, strategy: "absolute", placement: t.placement });
}
var cb = { name: "popperOffsets", enabled: true, phase: "read", fn: ub, data: {} }, db = { top: "auto", right: "auto", bottom: "auto", left: "auto" };
function pb(e, t) {
  var n = e.x, a = e.y, r = t.devicePixelRatio || 1;
  return { x: mr(n * r) / r || 0, y: mr(a * r) / r || 0 };
}
function tc(e) {
  var t, n = e.popper, a = e.popperRect, r = e.placement, l = e.variation, o = e.offsets, i = e.position, s = e.gpuAcceleration, c = e.adaptive, d = e.roundOffsets, u = e.isFixed, p = o.x, v = p === void 0 ? 0 : p, b = o.y, h = b === void 0 ? 0 : b, N = typeof d == "function" ? d({ x: v, y: h }) : { x: v, y: h };
  v = N.x, h = N.y;
  var I = o.hasOwnProperty("x"), x = o.hasOwnProperty("y"), _ = Kn, g = gn, R = window;
  if (c) {
    var M = bo(n), C = "clientHeight", Y = "clientWidth";
    if (M === nn(n) && (M = Ca(n), Un(M).position !== "static" && i === "absolute" && (C = "scrollHeight", Y = "scrollWidth")), M = M, r === gn || (r === Kn || r === ga) && l === nl) {
      g = $n;
      var P = u && M === R && R.visualViewport ? R.visualViewport.height : M[C];
      h -= P - a.height, h *= s ? 1 : -1;
    }
    if (r === Kn || (r === gn || r === $n) && l === nl) {
      _ = ga;
      var $ = u && M === R && R.visualViewport ? R.visualViewport.width : M[Y];
      v -= $ - a.width, v *= s ? 1 : -1;
    }
  }
  var H = Object.assign({ position: i }, c && db), z = d === true ? pb({ x: v, y: h }, nn(n)) : { x: v, y: h };
  if (v = z.x, h = z.y, s) {
    var se;
    return Object.assign({}, H, (se = {}, se[g] = x ? "0" : "", se[_] = I ? "0" : "", se.transform = (R.devicePixelRatio || 1) <= 1 ? "translate(" + v + "px, " + h + "px)" : "translate3d(" + v + "px, " + h + "px, 0)", se));
  }
  return Object.assign({}, H, (t = {}, t[g] = x ? h + "px" : "", t[_] = I ? v + "px" : "", t.transform = "", t));
}
function fb(e) {
  var t = e.state, n = e.options, a = n.gpuAcceleration, r = a === void 0 ? true : a, l = n.adaptive, o = l === void 0 ? true : l, i = n.roundOffsets, s = i === void 0 ? true : i, c = { placement: va(t.placement), variation: wr(t.placement), popper: t.elements.popper, popperRect: t.rects.popper, gpuAcceleration: r, isFixed: t.options.strategy === "fixed" };
  t.modifiersData.popperOffsets != null && (t.styles.popper = Object.assign({}, t.styles.popper, tc(Object.assign({}, c, { offsets: t.modifiersData.popperOffsets, position: t.options.strategy, adaptive: o, roundOffsets: s })))), t.modifiersData.arrow != null && (t.styles.arrow = Object.assign({}, t.styles.arrow, tc(Object.assign({}, c, { offsets: t.modifiersData.arrow, position: "absolute", adaptive: false, roundOffsets: s })))), t.attributes.popper = Object.assign({}, t.attributes.popper, { "data-popper-placement": t.placement });
}
var vb = { name: "computeStyles", enabled: true, phase: "beforeWrite", fn: fb, data: {} };
function mb(e) {
  var t = e.state;
  Object.keys(t.elements).forEach(function(n) {
    var a = t.styles[n] || {}, r = t.attributes[n] || {}, l = t.elements[n];
    !sn(l) || !Pn(l) || (Object.assign(l.style, a), Object.keys(r).forEach(function(o) {
      var i = r[o];
      i === false ? l.removeAttribute(o) : l.setAttribute(o, i === true ? "" : i);
    }));
  });
}
function hb(e) {
  var t = e.state, n = { popper: { position: t.options.strategy, left: "0", top: "0", margin: "0" }, arrow: { position: "absolute" }, reference: {} };
  return Object.assign(t.elements.popper.style, n.popper), t.styles = n, t.elements.arrow && Object.assign(t.elements.arrow.style, n.arrow), function() {
    Object.keys(t.elements).forEach(function(a) {
      var r = t.elements[a], l = t.attributes[a] || {}, o = Object.keys(t.styles.hasOwnProperty(a) ? t.styles[a] : n[a]), i = o.reduce(function(s, c) {
        return s[c] = "", s;
      }, {});
      !sn(r) || !Pn(r) || (Object.assign(r.style, i), Object.keys(l).forEach(function(s) {
        r.removeAttribute(s);
      }));
    });
  };
}
var gb = { name: "applyStyles", enabled: true, phase: "write", fn: mb, effect: hb, requires: ["computeStyles"] }, wb = [sb, cb, vb, gb], yb = ob({ defaultModifiers: wb });
function bb(e) {
  return e === "x" ? "y" : "x";
}
function Rl(e, t, n) {
  return ja(e, Kl(t, n));
}
function _b(e, t, n) {
  var a = Rl(e, t, n);
  return a > n ? n : a;
}
function xb(e) {
  var t = e.state, n = e.options, a = e.name, r = n.mainAxis, l = r === void 0 ? true : r, o = n.altAxis, i = o === void 0 ? false : o, s = n.boundary, c = n.rootBoundary, d = n.altBoundary, u = n.padding, p = n.tether, v = p === void 0 ? true : p, b = n.tetherOffset, h = b === void 0 ? 0 : b, N = ks(t, { boundary: s, rootBoundary: c, padding: u, altBoundary: d }), I = va(t.placement), x = wr(t.placement), _ = !x, g = Rp(I), R = bb(g), M = t.modifiersData.popperOffsets, C = t.rects.reference, Y = t.rects.popper, P = typeof h == "function" ? h(Object.assign({}, t.rects, { placement: t.placement })) : h, $ = typeof P == "number" ? { mainAxis: P, altAxis: P } : Object.assign({ mainAxis: 0, altAxis: 0 }, P), H = t.modifiersData.offset ? t.modifiersData.offset[t.placement] : null, z = { x: 0, y: 0 };
  if (M) {
    if (l) {
      var se, j = g === "y" ? gn : Kn, y = g === "y" ? $n : ga, V = g === "y" ? "height" : "width", w = M[g], ae = w + N[j], ce = w - N[y], ve = v ? -Y[V] / 2 : 0, _e = x === gr ? C[V] : Y[V], q = x === gr ? -Y[V] : -C[V], oe = t.elements.arrow, S = v && oe ? Op(oe) : { width: 0, height: 0 }, W = t.modifiersData["arrow#persistent"] ? t.modifiersData["arrow#persistent"].padding : Np(), G = W[j], D = W[y], fe = Rl(0, C[V], S[V]), Ae = _ ? C[V] / 2 - ve - fe - G - $.mainAxis : _e - fe - G - $.mainAxis, re = _ ? -C[V] / 2 + ve + fe + D + $.mainAxis : q + fe + D + $.mainAxis, Oe = t.elements.arrow && bo(t.elements.arrow), O = Oe ? g === "y" ? Oe.clientTop || 0 : Oe.clientLeft || 0 : 0, m = (se = H == null ? void 0 : H[g]) != null ? se : 0, k = w + Ae - m - O, E = w + re - m, B = Rl(v ? Kl(ae, k) : ae, w, v ? ja(ce, E) : ce);
      M[g] = B, z[g] = B - w;
    }
    if (i) {
      var X, A = g === "x" ? gn : Kn, U = g === "x" ? $n : ga, ee = M[R], le = R === "y" ? "height" : "width", ue = ee + N[A], ie = ee - N[U], be = [gn, Kn].indexOf(I) !== -1, xe = (X = H == null ? void 0 : H[R]) != null ? X : 0, De = be ? ue : ee - C[le] - Y[le] - xe + $.altAxis, Le = be ? ee + C[le] + Y[le] - xe - $.altAxis : ie, je = v && be ? _b(De, ee, Le) : Rl(v ? De : ue, ee, v ? Le : ie);
      M[R] = je, z[R] = je - ee;
    }
    t.modifiersData[a] = z;
  }
}
var kb = { name: "preventOverflow", enabled: true, phase: "main", fn: xb, requiresIfExists: ["offset"] }, Sb = { left: "right", right: "left", bottom: "top", top: "bottom" };
function El(e) {
  return e.replace(/left|right|bottom|top/g, function(t) {
    return Sb[t];
  });
}
var Cb = { start: "end", end: "start" };
function nc(e) {
  return e.replace(/start|end/g, function(t) {
    return Cb[t];
  });
}
function Mb(e, t) {
  t === void 0 && (t = {});
  var n = t, a = n.placement, r = n.boundary, l = n.rootBoundary, o = n.padding, i = n.flipVariations, s = n.allowedAutoPlacements, c = s === void 0 ? Iy : s, d = wr(a), u = d ? i ? Qu : Qu.filter(function(b) {
    return wr(b) === d;
  }) : _o, p = u.filter(function(b) {
    return c.indexOf(b) >= 0;
  });
  p.length === 0 && (p = u);
  var v = p.reduce(function(b, h) {
    return b[h] = ks(e, { placement: h, boundary: r, rootBoundary: l, padding: o })[va(h)], b;
  }, {});
  return Object.keys(v).sort(function(b, h) {
    return v[b] - v[h];
  });
}
function Tb(e) {
  if (va(e) === xs) return [];
  var t = El(e);
  return [nc(e), t, nc(t)];
}
function Ab(e) {
  var t = e.state, n = e.options, a = e.name;
  if (!t.modifiersData[a]._skip) {
    for (var r = n.mainAxis, l = r === void 0 ? true : r, o = n.altAxis, i = o === void 0 ? true : o, s = n.fallbackPlacements, c = n.padding, d = n.boundary, u = n.rootBoundary, p = n.altBoundary, v = n.flipVariations, b = v === void 0 ? true : v, h = n.allowedAutoPlacements, N = t.options.placement, I = va(N), x = I === N, _ = s || (x || !b ? [El(N)] : Tb(N)), g = [N].concat(_).reduce(function(S, W) {
      return S.concat(va(W) === xs ? Mb(t, { placement: W, boundary: d, rootBoundary: u, padding: c, flipVariations: b, allowedAutoPlacements: h }) : W);
    }, []), R = t.rects.reference, M = t.rects.popper, C = /* @__PURE__ */ new Map(), Y = true, P = g[0], $ = 0; $ < g.length; $++) {
      var H = g[$], z = va(H), se = wr(H) === gr, j = [gn, $n].indexOf(z) >= 0, y = j ? "width" : "height", V = ks(t, { placement: H, boundary: d, rootBoundary: u, altBoundary: p, padding: c }), w = j ? se ? ga : Kn : se ? $n : gn;
      R[y] > M[y] && (w = El(w));
      var ae = El(w), ce = [];
      if (l && ce.push(V[z] <= 0), i && ce.push(V[w] <= 0, V[ae] <= 0), ce.every(function(S) {
        return S;
      })) {
        P = H, Y = false;
        break;
      }
      C.set(H, ce);
    }
    if (Y) for (var ve = b ? 3 : 1, _e = function(S) {
      var W = g.find(function(G) {
        var D = C.get(G);
        if (D) return D.slice(0, S).every(function(fe) {
          return fe;
        });
      });
      if (W) return P = W, "break";
    }, q = ve; q > 0; q--) {
      var oe = _e(q);
      if (oe === "break") break;
    }
    t.placement !== P && (t.modifiersData[a]._skip = true, t.placement = P, t.reset = true);
  }
}
var Db = { name: "flip", enabled: true, phase: "main", fn: Ab, requiresIfExists: ["offset"], data: { _skip: false } };
function Lb(e, t, n) {
  const { disabled: a, appendTo: r, appendToBody: l, openDirection: o } = Pt(e), i = xa().proxy, s = n.multiselect, c = n.dropdown, d = te(false), u = te(null), p = te(null), v = st(() => r.value || l.value), b = st(() => o.value === "top" && p.value === "bottom" || o.value === "bottom" && p.value !== "top" ? "bottom" : "top"), h = () => {
    d.value || a.value || (d.value = true, t.emit("open", i), v.value && bt(() => {
      I();
    }));
  }, N = () => {
    d.value && (d.value = false, t.emit("close", i));
  }, I = () => {
    if (!u.value) return;
    let _ = parseInt(window.getComputedStyle(c.value).borderTopWidth.replace("px", "")), g = parseInt(window.getComputedStyle(c.value).borderBottomWidth.replace("px", ""));
    u.value.setOptions((R) => ({ ...R, modifiers: [...R.modifiers, { name: "offset", options: { offset: [0, (b.value === "top" ? _ : g) * -1] } }] })), u.value.update();
  }, x = (_) => {
    for (; _ && _ !== document.body; ) {
      if (getComputedStyle(_).position === "fixed") return true;
      _ = _.parentElement;
    }
    return false;
  };
  return ot(() => {
    v.value && (u.value = yb(s.value, c.value, { strategy: x(s.value) ? "fixed" : void 0, placement: o.value, modifiers: [kb, Db, { name: "sameWidth", enabled: true, phase: "beforeWrite", requires: ["computeStyles"], fn: ({ state: _ }) => {
      _.styles.popper.width = `${_.rects.reference.width}px`;
    }, effect: ({ state: _ }) => {
      _.elements.popper.style.width = `${_.elements.reference.offsetWidth}px`;
    } }, { name: "toggleClass", enabled: true, phase: "write", fn({ state: _ }) {
      p.value = _.placement;
    } }] }));
  }), ro(() => {
    !v.value || !u.value || (u.value.destroy(), u.value = null);
  }), { popper: u, isOpen: d, open: h, close: N, placement: b, updatePopper: I };
}
function Ob(e, t, n) {
  const { searchable: a, disabled: r, clearOnBlur: l } = Pt(e), o = n.input, i = n.open, s = n.close, c = n.clearSearch, d = n.isOpen, u = n.wrapper, p = n.tags, v = te(false), b = te(false), h = st(() => a.value || r.value ? -1 : 0), N = () => {
    a.value && o.value.blur(), u.value.blur();
  }, I = () => {
    a.value && !r.value && o.value.focus();
  }, x = (g = true) => {
    r.value || (v.value = true, g && i());
  }, _ = () => {
    v.value = false, setTimeout(() => {
      v.value || (s(), l.value && c());
    }, 1);
  };
  return { tabindex: h, isActive: v, mouseClicked: b, blur: N, focus: I, activate: x, deactivate: _, handleFocusIn: (g) => {
    g.target.closest("[data-tags]") && g.target.nodeName !== "INPUT" || g.target.closest("[data-clear]") || x(b.value);
  }, handleFocusOut: () => {
    _();
  }, handleCaretClick: () => {
    _(), N();
  }, handleMousedown: (g) => {
    b.value = true, d.value && (g.target.isEqualNode(u.value) || g.target.isEqualNode(p.value)) ? setTimeout(() => {
      _();
    }, 0) : !d.value && (document.activeElement.isEqualNode(u.value) || document.activeElement.isEqualNode(o.value)) && x(), setTimeout(() => {
      b.value = false;
    }, 0);
  } };
}
function Pb(e, t, n) {
  const { mode: a, addTagOn: r, openDirection: l, searchable: o, showOptions: i, valueProp: s, groups: c, addOptionOn: d, createTag: u, createOption: p, reverse: v } = Pt(e), b = xa().proxy, h = n.iv, N = n.update, I = n.deselect, x = n.search, _ = n.setPointer, g = n.selectPointer, R = n.backwardPointer, M = n.forwardPointer, C = n.multiselect, Y = n.wrapper, P = n.tags, $ = n.isOpen, H = n.open, z = n.blur, se = n.fo, j = st(() => u.value || p.value || false), y = st(() => r.value !== void 0 ? r.value : d.value !== void 0 ? d.value : ["enter"]), V = () => {
    a.value === "tags" && !i.value && j.value && o.value && !c.value && _(se.value[se.value.map((w) => w[s.value]).indexOf(x.value)]);
  };
  return { handleKeydown: (w) => {
    t.emit("keydown", w, b);
    let ae, ce;
    switch (["ArrowLeft", "ArrowRight", "Enter"].indexOf(w.key) !== -1 && a.value === "tags" && (ae = [...C.value.querySelectorAll("[data-tags] > *")].filter((ve) => ve !== P.value), ce = ae.findIndex((ve) => ve === document.activeElement)), w.key) {
      case "Backspace":
        if (a.value === "single" || o.value && [null, ""].indexOf(x.value) === -1 || h.value.length === 0) return;
        let ve = h.value.filter((_e) => !_e.disabled && _e.remove !== false);
        ve.length && I(ve[ve.length - 1]);
        break;
      case "Enter":
        if (w.preventDefault(), w.keyCode === 229) return;
        if (ce !== -1 && ce !== void 0) {
          N([...h.value].filter((_e, q) => q !== ce)), ce === ae.length - 1 && (ae.length - 1 ? ae[ae.length - 2].focus() : o.value ? P.value.querySelector("input").focus() : Y.value.focus());
          return;
        }
        if (y.value.indexOf("enter") === -1 && j.value) return;
        V(), g();
        break;
      case " ":
        if (!j.value && !o.value) {
          w.preventDefault(), V(), g();
          return;
        }
        if (!j.value) return false;
        if (y.value.indexOf("space") === -1 && j.value) return;
        w.preventDefault(), V(), g();
        break;
      case "Tab":
      case ";":
      case ",":
        if (y.value.indexOf(w.key.toLowerCase()) === -1 || !j.value) return;
        V(), g(), w.preventDefault();
        break;
      case "Escape":
        z();
        break;
      case "ArrowUp":
        if (w.preventDefault(), !i.value) return;
        $.value || H(), R();
        break;
      case "ArrowDown":
        if (w.preventDefault(), !i.value) return;
        $.value || H(), M();
        break;
      case "ArrowLeft":
        if (o.value && P.value && P.value.querySelector("input").selectionStart || w.shiftKey || a.value !== "tags" || !h.value || !h.value.length) return;
        w.preventDefault(), ce === -1 ? ae[ae.length - 1].focus() : ce > 0 && ae[ce - 1].focus();
        break;
      case "ArrowRight":
        if (ce === -1 || w.shiftKey || a.value !== "tags" || !h.value || !h.value.length) return;
        w.preventDefault(), ae.length > ce + 1 ? ae[ce + 1].focus() : o.value ? P.value.querySelector("input").focus() : o.value || Y.value.focus();
        break;
    }
  }, handleKeyup: (w) => {
    t.emit("keyup", w, b);
  }, preparePointer: V };
}
function $b(e, t, n) {
  const { classes: a, disabled: r, showOptions: l, breakTags: o } = Pt(e), i = n.isOpen, s = n.isPointed, c = n.isSelected, d = n.isDisabled, u = n.isActive, p = n.canPointGroups, v = n.resolving, b = n.fo, h = n.placement, N = st(() => ({ container: "multiselect", containerDisabled: "is-disabled", containerOpen: "is-open", containerOpenTop: "is-open-top", containerActive: "is-active", wrapper: "multiselect-wrapper", singleLabel: "multiselect-single-label", singleLabelText: "multiselect-single-label-text", multipleLabel: "multiselect-multiple-label", search: "multiselect-search", tags: "multiselect-tags", tag: "multiselect-tag", tagWrapper: "multiselect-tag-wrapper", tagWrapperBreak: "multiselect-tag-wrapper-break", tagDisabled: "is-disabled", tagRemove: "multiselect-tag-remove", tagRemoveIcon: "multiselect-tag-remove-icon", tagsSearchWrapper: "multiselect-tags-search-wrapper", tagsSearch: "multiselect-tags-search", tagsSearchCopy: "multiselect-tags-search-copy", placeholder: "multiselect-placeholder", caret: "multiselect-caret", caretOpen: "is-open", clear: "multiselect-clear", clearIcon: "multiselect-clear-icon", spinner: "multiselect-spinner", inifinite: "multiselect-inifite", inifiniteSpinner: "multiselect-inifite-spinner", dropdown: "multiselect-dropdown", dropdownTop: "is-top", dropdownHidden: "is-hidden", options: "multiselect-options", optionsTop: "is-top", group: "multiselect-group", groupLabel: "multiselect-group-label", groupLabelPointable: "is-pointable", groupLabelPointed: "is-pointed", groupLabelSelected: "is-selected", groupLabelDisabled: "is-disabled", groupLabelSelectedPointed: "is-selected is-pointed", groupLabelSelectedDisabled: "is-selected is-disabled", groupOptions: "multiselect-group-options", option: "multiselect-option", optionPointed: "is-pointed", optionSelected: "is-selected", optionDisabled: "is-disabled", optionSelectedPointed: "is-selected is-pointed", optionSelectedDisabled: "is-selected is-disabled", noOptions: "multiselect-no-options", noResults: "multiselect-no-results", fakeInput: "multiselect-fake-input", assist: "multiselect-assistive-text", spacer: "multiselect-spacer", ...a.value })), I = st(() => !!(i.value && l.value && (!v.value || v.value && b.value.length)));
  return { classList: J(() => {
    const x = N.value;
    return { container: [x.container].concat(r.value ? x.containerDisabled : []).concat(I.value && h.value === "top" ? x.containerOpenTop : []).concat(I.value && h.value !== "top" ? x.containerOpen : []).concat(u.value ? x.containerActive : []), wrapper: x.wrapper, spacer: x.spacer, singleLabel: x.singleLabel, singleLabelText: x.singleLabelText, multipleLabel: x.multipleLabel, search: x.search, tags: x.tags, tag: [x.tag].concat(r.value ? x.tagDisabled : []), tagWrapper: [x.tagWrapper, o.value ? x.tagWrapperBreak : null], tagDisabled: x.tagDisabled, tagRemove: x.tagRemove, tagRemoveIcon: x.tagRemoveIcon, tagsSearchWrapper: x.tagsSearchWrapper, tagsSearch: x.tagsSearch, tagsSearchCopy: x.tagsSearchCopy, placeholder: x.placeholder, caret: [x.caret].concat(i.value ? x.caretOpen : []), clear: x.clear, clearIcon: x.clearIcon, spinner: x.spinner, inifinite: x.inifinite, inifiniteSpinner: x.inifiniteSpinner, dropdown: [x.dropdown].concat(h.value === "top" ? x.dropdownTop : []).concat(!i.value || !l.value || !I.value ? x.dropdownHidden : []), options: [x.options].concat(h.value === "top" ? x.optionsTop : []), group: x.group, groupLabel: (_) => {
      let g = [x.groupLabel];
      return s(_) ? g.push(c(_) ? x.groupLabelSelectedPointed : x.groupLabelPointed) : c(_) && p.value ? g.push(d(_) ? x.groupLabelSelectedDisabled : x.groupLabelSelected) : d(_) && g.push(x.groupLabelDisabled), p.value && g.push(x.groupLabelPointable), g;
    }, groupOptions: x.groupOptions, option: (_, g) => {
      let R = [x.option];
      return s(_) ? R.push(c(_) ? x.optionSelectedPointed : x.optionPointed) : c(_) ? R.push(d(_) ? x.optionSelectedDisabled : x.optionSelected) : (d(_) || g && d(g)) && R.push(x.optionDisabled), R;
    }, noOptions: x.noOptions, noResults: x.noResults, assist: x.assist, fakeInput: x.fakeInput };
  }), showDropdown: I };
}
function Rb(e, t, n) {
  const { limit: a, infinite: r } = Pt(e), l = n.isOpen, o = n.offset, i = n.search, s = n.pfo, c = n.eo, d = te(null), u = Qa(null), p = st(() => o.value < s.value.length), v = (h) => {
    const { isIntersecting: N, target: I } = h[0];
    if (N) {
      const x = I.offsetParent, _ = x.scrollTop;
      o.value += a.value == -1 ? 10 : a.value, bt(() => {
        x.scrollTop = _;
      });
    }
  }, b = () => {
    l.value && o.value < s.value.length ? d.value.observe(u.value) : !l.value && d.value && d.value.disconnect();
  };
  return He(l, () => {
    r.value && b();
  }), He(i, () => {
    r.value && (o.value = a.value, b());
  }, { flush: "post" }), He(c, () => {
    r.value && b();
  }, { immediate: false, flush: "post" }), ot(() => {
    window && window.IntersectionObserver && (d.value = new IntersectionObserver(v));
  }), { hasMore: p, infiniteLoader: u };
}
function Eb(e, t, n) {
  const { placeholder: a, id: r, valueProp: l, label: o, mode: i, groupLabel: s, aria: c, searchable: d } = Pt(e), u = n.pointer, p = n.iv, v = n.hasSelected, b = n.multipleLabelText, h = te(null), N = st(() => `${r.value ? r.value + "-" : ""}assist`), I = st(() => `${r.value ? r.value + "-" : ""}multiselect-options`), x = st(() => {
    if (u.value) {
      let z = r.value ? `${r.value}-` : "";
      return z += `${u.value.group ? "multiselect-group" : "multiselect-option"}-`, z += u.value.group ? u.value.index : u.value[l.value], z;
    }
  }), _ = st(() => a.value), g = st(() => i.value !== "single"), R = J(() => i.value === "single" && v.value ? p.value[o.value] : i.value === "multiple" && v.value ? b.value : i.value === "tags" && v.value ? p.value.map((z) => z[o.value]).join(", ") : ""), M = J(() => {
    let z = { ...c.value };
    return d.value && (z["aria-labelledby"] = z["aria-labelledby"] ? `${N.value} ${z["aria-labelledby"]}` : N.value, R.value && z["aria-label"] && (z["aria-label"] = `${R.value}, ${z["aria-label"]}`)), z;
  }), C = (z) => `${r.value ? r.value + "-" : ""}multiselect-option-${z[l.value]}`, Y = (z) => `${r.value ? r.value + "-" : ""}multiselect-group-${z.index}`, P = (z) => `${z}`, $ = (z) => `${z}`, H = (z) => `${z} ❎`;
  return ot(() => {
    if (r.value && document && document.querySelector) {
      let z = document.querySelector(`[for="${r.value}"]`);
      h.value = z ? z.innerText : null;
    }
  }), { arias: M, ariaLabel: R, ariaAssist: N, ariaControls: I, ariaPlaceholder: _, ariaMultiselectable: g, ariaActiveDescendant: x, ariaOptionId: C, ariaOptionLabel: P, ariaGroupId: Y, ariaGroupLabel: $, ariaTagLabel: H };
}
function Nb(e, t, n) {
  const { locale: a, fallbackLocale: r } = Pt(e);
  return { localize: (l) => !l || typeof l != "object" ? l : l && l[a.value] ? l[a.value] : l && a.value && l[a.value.toUpperCase()] ? l[a.value.toUpperCase()] : l && l[r.value] ? l[r.value] : l && r.value && l[r.value.toUpperCase()] ? l[r.value.toUpperCase()] : l && Object.keys(l)[0] ? l[Object.keys(l)[0]] : "" };
}
function Ib(e, t, n) {
  const a = Qa(null), r = Qa(null), l = Qa(null), o = Qa(null), i = Qa(null);
  return { multiselect: a, wrapper: r, tags: l, input: o, dropdown: i };
}
function Vb(e, t, n, a = {}) {
  return n.forEach((r) => {
    a = { ...a, ...r(e, t, a) };
  }), a;
}
var Ss = { name: "Multiselect", emits: ["paste", "open", "close", "select", "deselect", "input", "search-change", "tag", "option", "update:modelValue", "change", "clear", "keydown", "keyup", "max", "create"], props: { value: { required: false }, modelValue: { required: false }, options: { type: [Array, Object, Function], required: false, default: () => [] }, id: { type: [String, Number], required: false, default: void 0 }, name: { type: [String, Number], required: false, default: "multiselect" }, disabled: { type: Boolean, required: false, default: false }, label: { type: String, required: false, default: "label" }, trackBy: { type: [String, Array], required: false, default: void 0 }, valueProp: { type: String, required: false, default: "value" }, placeholder: { type: String, required: false, default: null }, mode: { type: String, required: false, default: "single" }, searchable: { type: Boolean, required: false, default: false }, limit: { type: Number, required: false, default: -1 }, hideSelected: { type: Boolean, required: false, default: true }, createTag: { type: Boolean, required: false, default: void 0 }, createOption: { type: Boolean, required: false, default: void 0 }, appendNewTag: { type: Boolean, required: false, default: void 0 }, appendNewOption: { type: Boolean, required: false, default: void 0 }, addTagOn: { type: Array, required: false, default: void 0 }, addOptionOn: { type: Array, required: false, default: void 0 }, caret: { type: Boolean, required: false, default: true }, loading: { type: Boolean, required: false, default: false }, noOptionsText: { type: [String, Object], required: false, default: "The list is empty" }, noResultsText: { type: [String, Object], required: false, default: "No results found" }, multipleLabel: { type: Function, required: false, default: void 0 }, object: { type: Boolean, required: false, default: false }, delay: { type: Number, required: false, default: -1 }, minChars: { type: Number, required: false, default: 0 }, resolveOnLoad: { type: Boolean, required: false, default: true }, filterResults: { type: Boolean, required: false, default: true }, clearOnSearch: { type: Boolean, required: false, default: false }, clearOnSelect: { type: Boolean, required: false, default: true }, canDeselect: { type: Boolean, required: false, default: true }, canClear: { type: Boolean, required: false, default: true }, max: { type: Number, required: false, default: -1 }, showOptions: { type: Boolean, required: false, default: true }, required: { type: Boolean, required: false, default: false }, openDirection: { type: String, required: false, default: "bottom" }, nativeSupport: { type: Boolean, required: false, default: false }, classes: { type: Object, required: false, default: () => ({}) }, strict: { type: Boolean, required: false, default: true }, closeOnSelect: { type: Boolean, required: false, default: true }, closeOnDeselect: { type: Boolean, required: false, default: false }, autocomplete: { type: String, required: false, default: void 0 }, groups: { type: Boolean, required: false, default: false }, groupLabel: { type: String, required: false, default: "label" }, groupOptions: { type: String, required: false, default: "options" }, groupHideEmpty: { type: Boolean, required: false, default: false }, groupSelect: { type: Boolean, required: false, default: true }, inputType: { type: String, required: false, default: "text" }, attrs: { required: false, type: Object, default: () => ({}) }, onCreate: { required: false, type: Function, default: void 0 }, disabledProp: { type: String, required: false, default: "disabled" }, searchStart: { type: Boolean, required: false, default: false }, reverse: { type: Boolean, required: false, default: false }, regex: { type: [Object, String, RegExp], required: false, default: void 0 }, rtl: { type: Boolean, required: false, default: false }, infinite: { type: Boolean, required: false, default: false }, aria: { required: false, type: Object, default: () => ({}) }, clearOnBlur: { required: false, type: Boolean, default: true }, locale: { required: false, type: String, default: null }, fallbackLocale: { required: false, type: String, default: "en" }, searchFilter: { required: false, type: Function, default: null }, allowAbsent: { required: false, type: Boolean, default: false }, appendToBody: { required: false, type: Boolean, default: false }, closeOnScroll: { required: false, type: Boolean, default: false }, breakTags: { required: false, type: Boolean, default: false }, appendTo: { required: false, type: String, default: void 0 } }, setup(e, t) {
  return Vb(e, t, [Ib, Nb, xy, Sy, Lb, ky, _y, Ob, Ty, Rb, Ay, Pb, $b, Eb]);
}, beforeMount() {
  (this.$root.constructor && this.$root.constructor.version && this.$root.constructor.version.match(/^2\./) || this.vueVersionMs === 2) && (this.$options.components.Teleport || (this.$options.components.Teleport = { render() {
    return this.$slots.default ? this.$slots.default[0] : null;
  } }));
} };
const jb = ["id", "dir"], Bb = ["tabindex", "aria-controls", "aria-placeholder", "aria-expanded", "aria-activedescendant", "aria-multiselectable", "role"], Fb = ["type", "modelValue", "value", "autocomplete", "id", "aria-controls", "aria-placeholder", "aria-expanded", "aria-activedescendant", "aria-multiselectable"], Yb = ["onKeyup", "aria-label"], qb = ["onClick"], zb = ["type", "modelValue", "value", "id", "autocomplete", "aria-controls", "aria-placeholder", "aria-expanded", "aria-activedescendant", "aria-multiselectable"], Hb = ["innerHTML"], Kb = ["id"], Zb = ["id"], Wb = ["id", "aria-label", "aria-selected"], Ub = ["data-pointed", "onMouseenter", "onClick"], Gb = ["innerHTML"], Qb = ["aria-label"], Xb = ["data-pointed", "data-selected", "onMouseenter", "onClick", "id", "aria-selected", "aria-label"], Jb = ["data-pointed", "data-selected", "onMouseenter", "onClick", "id", "aria-selected", "aria-label"], e2 = ["innerHTML"], t2 = ["innerHTML"], n2 = ["value"], a2 = ["name", "value"], r2 = ["name", "value"], l2 = ["id"];
function o2(e, t, n, a, r, l) {
  return T(), F("div", { ref: "multiselect", class: pe(e.classList.container), id: n.searchable ? void 0 : n.id, dir: n.rtl ? "rtl" : void 0, onFocusin: t[12] || (t[12] = (...o) => e.handleFocusIn && e.handleFocusIn(...o)), onFocusout: t[13] || (t[13] = (...o) => e.handleFocusOut && e.handleFocusOut(...o)), onKeyup: t[14] || (t[14] = (...o) => e.handleKeyup && e.handleKeyup(...o)), onKeydown: t[15] || (t[15] = (...o) => e.handleKeydown && e.handleKeydown(...o)) }, [L("div", ft({ class: e.classList.wrapper, onMousedown: t[9] || (t[9] = (...o) => e.handleMousedown && e.handleMousedown(...o)), ref: "wrapper", tabindex: e.tabindex, "aria-controls": n.searchable ? void 0 : e.ariaControls, "aria-placeholder": n.searchable ? void 0 : e.ariaPlaceholder, "aria-expanded": n.searchable ? void 0 : e.isOpen, "aria-activedescendant": n.searchable ? void 0 : e.ariaActiveDescendant, "aria-multiselectable": n.searchable ? void 0 : e.ariaMultiselectable, role: n.searchable ? void 0 : "combobox" }, n.searchable ? {} : e.arias), [Z(" Search "), n.mode !== "tags" && n.searchable && !n.disabled ? (T(), F("input", ft({ key: 0, type: n.inputType, modelValue: e.search, value: e.search, class: e.classList.search, autocomplete: n.autocomplete, id: n.searchable ? n.id : void 0, onInput: t[0] || (t[0] = (...o) => e.handleSearchInput && e.handleSearchInput(...o)), onKeypress: t[1] || (t[1] = (...o) => e.handleKeypress && e.handleKeypress(...o)), onPaste: t[2] || (t[2] = da((...o) => e.handlePaste && e.handlePaste(...o), ["stop"])), ref: "input", "aria-controls": e.ariaControls, "aria-placeholder": e.ariaPlaceholder, "aria-expanded": e.isOpen, "aria-activedescendant": e.ariaActiveDescendant, "aria-multiselectable": e.ariaMultiselectable, role: "combobox" }, { ...n.attrs, ...e.arias }), null, 16, Fb)) : Z("v-if", true), Z(" Tags (with search) "), n.mode == "tags" ? (T(), F("div", { key: 1, class: pe(e.classList.tags), "data-tags": "" }, [(T(true), F(Ce, null, Ve(e.iv, (o, i, s) => ye(e.$slots, "tag", { option: o, handleTagRemove: e.handleTagRemove, disabled: n.disabled }, () => [(T(), F("span", { class: pe([e.classList.tag, o.disabled ? e.classList.tagDisabled : null]), tabindex: "-1", onKeyup: gi((c) => e.handleTagRemove(o, c), ["enter"]), key: s, "aria-label": e.ariaTagLabel(e.localize(o[n.label])) }, [L("span", { class: pe(e.classList.tagWrapper) }, ge(e.localize(o[n.label])), 3), !n.disabled && !o.disabled ? (T(), F("span", { key: 0, class: pe(e.classList.tagRemove), onClick: da((c) => e.handleTagRemove(o, c), ["stop"]) }, [L("span", { class: pe(e.classList.tagRemoveIcon) }, null, 2)], 10, qb)) : Z("v-if", true)], 42, Yb))])), 256)), L("div", { class: pe(e.classList.tagsSearchWrapper), ref: "tags" }, [Z(" Used for measuring search width "), L("span", { class: pe(e.classList.tagsSearchCopy) }, ge(e.search), 3), Z(" Actual search input "), n.searchable && !n.disabled ? (T(), F("input", ft({ key: 0, type: n.inputType, modelValue: e.search, value: e.search, class: e.classList.tagsSearch, id: n.searchable ? n.id : void 0, autocomplete: n.autocomplete, onInput: t[3] || (t[3] = (...o) => e.handleSearchInput && e.handleSearchInput(...o)), onKeypress: t[4] || (t[4] = (...o) => e.handleKeypress && e.handleKeypress(...o)), onPaste: t[5] || (t[5] = da((...o) => e.handlePaste && e.handlePaste(...o), ["stop"])), ref: "input", "aria-controls": e.ariaControls, "aria-placeholder": e.ariaPlaceholder, "aria-expanded": e.isOpen, "aria-activedescendant": e.ariaActiveDescendant, "aria-multiselectable": e.ariaMultiselectable, role: "combobox" }, { ...n.attrs, ...e.arias }), null, 16, zb)) : Z("v-if", true)], 2)], 2)) : Z("v-if", true), Z(" Single label "), n.mode == "single" && e.hasSelected && !e.search && e.iv ? ye(e.$slots, "singlelabel", { key: 2, value: e.iv }, () => [L("div", { class: pe(e.classList.singleLabel) }, [L("span", { class: pe(e.classList.singleLabelText) }, ge(e.localize(e.iv[n.label])), 3)], 2)]) : Z("v-if", true), Z(" Multiple label "), n.mode == "multiple" && e.hasSelected && !e.search ? ye(e.$slots, "multiplelabel", { key: 3, values: e.iv }, () => [L("div", { class: pe(e.classList.multipleLabel), innerHTML: e.multipleLabelText }, null, 10, Hb)]) : Z("v-if", true), Z(" Placeholder "), n.placeholder && !e.hasSelected && !e.search ? ye(e.$slots, "placeholder", { key: 4 }, () => [L("div", { class: pe(e.classList.placeholder), "aria-hidden": "true" }, ge(n.placeholder), 3)]) : Z("v-if", true), Z(" Spinner "), n.loading || e.resolving ? ye(e.$slots, "spinner", { key: 5 }, () => [L("span", { class: pe(e.classList.spinner), "aria-hidden": "true" }, null, 2)]) : Z("v-if", true), Z(" Clear "), e.hasSelected && !n.disabled && n.canClear && !e.busy ? ye(e.$slots, "clear", { key: 6, clear: e.clear }, () => [L("span", { "aria-hidden": "true", tabindex: "0", role: "button", "data-clear": "", "aria-roledescription": "❎", class: pe(e.classList.clear), onClick: t[6] || (t[6] = (...o) => e.clear && e.clear(...o)), onKeyup: t[7] || (t[7] = gi((...o) => e.clear && e.clear(...o), ["enter"])) }, [L("span", { class: pe(e.classList.clearIcon) }, null, 2)], 34)]) : Z("v-if", true), Z(" Caret "), n.caret && n.showOptions ? ye(e.$slots, "caret", { key: 7, handleCaretClick: e.handleCaretClick, isOpen: e.isOpen }, () => [L("span", { class: pe(e.classList.caret), onClick: t[8] || (t[8] = (...o) => e.handleCaretClick && e.handleCaretClick(...o)), "aria-hidden": "true" }, null, 2)]) : Z("v-if", true)], 16, Bb), Z(" Options "), (T(), Pe(qc, { to: n.appendTo || "body", disabled: !n.appendToBody && !n.appendTo }, [L("div", { id: n.id ? `${n.id}-dropdown` : void 0, class: pe(e.classList.dropdown), tabindex: "-1", ref: "dropdown", onFocusin: t[10] || (t[10] = (...o) => e.handleFocusIn && e.handleFocusIn(...o)), onFocusout: t[11] || (t[11] = (...o) => e.handleFocusOut && e.handleFocusOut(...o)) }, [ye(e.$slots, "beforelist", { options: e.fo }), L("ul", { class: pe(e.classList.options), id: e.ariaControls, role: "listbox" }, [n.groups ? (T(true), F(Ce, { key: 0 }, Ve(e.fg, (o, i, s) => (T(), F("li", { class: pe(e.classList.group), key: s, id: e.ariaGroupId(o), "aria-label": e.ariaGroupLabel(e.localize(o[n.groupLabel])), "aria-selected": e.isSelected(o), role: "option" }, [o.__CREATE__ ? Z("v-if", true) : (T(), F("div", { key: 0, class: pe(e.classList.groupLabel(o)), "data-pointed": e.isPointed(o), onMouseenter: (c) => e.setPointer(o, i), onClick: (c) => e.handleGroupClick(o) }, [ye(e.$slots, "grouplabel", { group: o, isSelected: e.isSelected, isPointed: e.isPointed }, () => [L("span", { innerHTML: e.localize(o[n.groupLabel]) }, null, 8, Gb)])], 42, Ub)), L("ul", { class: pe(e.classList.groupOptions), "aria-label": e.ariaGroupLabel(e.localize(o[n.groupLabel])), role: "group" }, [(T(true), F(Ce, null, Ve(o.__VISIBLE__, (c, d, u) => (T(), F("li", { class: pe(e.classList.option(c, o)), "data-pointed": e.isPointed(c), "data-selected": e.isSelected(c) || void 0, key: u, onMouseenter: (p) => e.setPointer(c), onClick: (p) => e.handleOptionClick(c), id: e.ariaOptionId(c), "aria-selected": e.isSelected(c), "aria-label": e.ariaOptionLabel(e.localize(c[n.label])), role: "option" }, [ye(e.$slots, "option", { option: c, isSelected: e.isSelected, isPointed: e.isPointed, search: e.search }, () => [L("span", null, ge(e.localize(c[n.label])), 1)])], 42, Xb))), 128))], 10, Qb)], 10, Wb))), 128)) : (T(true), F(Ce, { key: 1 }, Ve(e.fo, (o, i, s) => (T(), F("li", { class: pe(e.classList.option(o)), "data-pointed": e.isPointed(o), "data-selected": e.isSelected(o) || void 0, key: s, onMouseenter: (c) => e.setPointer(o), onClick: (c) => e.handleOptionClick(o), id: e.ariaOptionId(o), "aria-selected": e.isSelected(o), "aria-label": e.ariaOptionLabel(e.localize(o[n.label])), role: "option" }, [ye(e.$slots, "option", { option: o, isSelected: e.isSelected, isPointed: e.isPointed, search: e.search }, () => [L("span", null, ge(e.localize(o[n.label])), 1)])], 42, Jb))), 128))], 10, Zb), e.noOptions ? ye(e.$slots, "nooptions", { key: 0 }, () => [L("div", { class: pe(e.classList.noOptions), innerHTML: e.localize(n.noOptionsText) }, null, 10, e2)]) : Z("v-if", true), e.noResults ? ye(e.$slots, "noresults", { key: 1 }, () => [L("div", { class: pe(e.classList.noResults), innerHTML: e.localize(n.noResultsText) }, null, 10, t2)]) : Z("v-if", true), n.infinite && e.hasMore ? (T(), F("div", { key: 2, class: pe(e.classList.inifinite), ref: "infiniteLoader" }, [ye(e.$slots, "infinite", {}, () => [L("span", { class: pe(e.classList.inifiniteSpinner) }, null, 2)])], 2)) : Z("v-if", true), ye(e.$slots, "afterlist", { options: e.fo })], 42, Kb)], 8, ["to", "disabled"])), Z(" Hacky input element to show HTML5 required warning "), n.required ? (T(), F("input", { key: 0, class: pe(e.classList.fakeInput), tabindex: "-1", value: e.textValue, required: "" }, null, 10, n2)) : Z("v-if", true), Z(" Native input support "), n.nativeSupport ? (T(), F(Ce, { key: 1 }, [n.mode == "single" ? (T(), F("input", { key: 0, type: "hidden", name: n.name, value: e.plainValue !== void 0 ? e.plainValue : "" }, null, 8, a2)) : (T(true), F(Ce, { key: 1 }, Ve(e.plainValue, (o, i) => (T(), F("input", { type: "hidden", name: `${n.name}[]`, value: o, key: i }, null, 8, r2))), 128))], 64)) : Z("v-if", true), Z(" Screen reader assistive text "), n.searchable && e.hasSelected ? (T(), F("div", { key: 2, class: pe(e.classList.assist), id: e.ariaAssist, "aria-hidden": "true" }, ge(e.ariaLabel), 11, l2)) : Z("v-if", true), Z(" Create height for empty input "), L("div", { class: pe(e.classList.spacer) }, null, 2)], 42, jb);
}
Ss.render = o2;
Ss.__file = "src/Multiselect.vue";
const xo = sl("fieldDependency", { state: () => ({ modifierFields: {}, hiddenFields: [], modifierFieldStatus: {} }) }), Cs = (e, t) => {
  const n = e.__vccOpts || e;
  for (const [a, r] of t) n[a] = r;
  return n;
}, i2 = {}, s2 = { width: "20", height: "15", viewBox: "0 0 20 15", fill: "none", xmlns: "http://www.w3.org/2000/svg" };
function u2(e, t) {
  return T(), F("svg", s2, t[0] || (t[0] = [L("path", { d: "M19.2131 4.11564C19.2161 4.16916 19.2121 4.22364 19.1983 4.27775L17.9646 10.5323C17.9024 10.7741 17.6796 10.9441 17.4235 10.9455L10.0216 10.9818H10.0188H2.61682C2.35933 10.9818 2.13495 10.8112 2.07275 10.5681L0.839103 4.29542C0.824897 4.23985 0.820785 4.18385 0.824374 4.12895C0.34714 3.98269 0 3.54829 0 3.03636C0 2.40473 0.528224 1.89091 1.17757 1.89091C1.82692 1.89091 2.35514 2.40473 2.35514 3.03636C2.35514 3.39207 2.18759 3.71033 1.92523 3.92058L3.46976 5.43433C3.86011 5.81695 4.40179 6.03629 4.95596 6.03629C5.61122 6.03629 6.23596 5.7336 6.62938 5.22647L9.1677 1.95491C8.95447 1.74764 8.82243 1.46124 8.82243 1.14545C8.82243 0.513818 9.35065 0 10 0C10.6493 0 11.1776 0.513818 11.1776 1.14545C11.1776 1.45178 11.0526 1.72982 10.8505 1.93556L10.8526 1.93811L13.3726 5.21869C13.7658 5.73069 14.3928 6.03636 15.0499 6.03636C15.6092 6.03636 16.1351 5.82451 16.5305 5.43978L18.0848 3.92793C17.8169 3.71775 17.6449 3.39644 17.6449 3.03636C17.6449 2.40473 18.1731 1.89091 18.8224 1.89091C19.4718 1.89091 20 2.40473 20 3.03636C20 3.53462 19.6707 3.9584 19.2131 4.11564ZM17.8443 12.6909C17.8443 12.3897 17.5932 12.1455 17.2835 12.1455H2.77884C2.46916 12.1455 2.21809 12.3897 2.21809 12.6909V14C2.21809 14.3012 2.46916 14.5455 2.77884 14.5455H17.2835C17.5932 14.5455 17.8443 14.3012 17.8443 14V12.6909Z", fill: "#FB9A28" }, null, -1)]));
}
const Ip = Cs(i2, [["render", u2]]), c2 = {}, d2 = { class: "wpuf-pro-field-tooltip", style: { left: "50%", top: "-0.5em" } };
function p2(e, t) {
  return T(), F("div", d2, t[0] || (t[0] = [nv('<h3 class="tooltip-header">Available in Pro. Also enjoy:</h3><ul><li class="wpuf-flex wpuf-items-center"><span class="tooltip-check"><svg width="10" height="8" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8.92671 1.13426C8.59667 0.804188 8.06162 0.804234 7.73159 1.13423L3.37421 5.49165L1.89718 4.01462C1.56712 3.68454 1.03208 3.68467 0.702082 4.0146C0.372021 4.34463 0.372046 4.8797 0.702068 5.20972L2.77666 7.28428C3.10675 7.61442 3.64199 7.61406 3.97177 7.28428L8.92668 2.32937C9.25676 1.99933 9.25668 1.46426 8.92671 1.13426ZM0.992017 4.85283C1.00166 4.86513 1.01215 4.87698 1.02348 4.88831L3.09807 6.96287C3.25053 7.11537 3.49796 7.11527 3.65036 6.96287L8.60528 2.00795C8.74649 1.86675 8.75695 1.64433 8.6367 1.49107C8.7569 1.64433 8.74643 1.86671 8.60524 2.00789L3.65032 6.96281C3.49792 7.11521 3.25048 7.11532 3.09803 6.96281L1.02343 4.88825C1.01212 4.87694 1.00165 4.86511 0.992017 4.85283Z" fill="white"></path></svg></span> 24/7 Priority Support</li><li class="wpuf-flex wpuf-items-center"><span class="tooltip-check"><svg width="10" height="8" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8.92671 1.13426C8.59667 0.804188 8.06162 0.804234 7.73159 1.13423L3.37421 5.49165L1.89718 4.01462C1.56712 3.68454 1.03208 3.68467 0.702082 4.0146C0.372021 4.34463 0.372046 4.8797 0.702068 5.20972L2.77666 7.28428C3.10675 7.61442 3.64199 7.61406 3.97177 7.28428L8.92668 2.32937C9.25676 1.99933 9.25668 1.46426 8.92671 1.13426ZM0.992017 4.85283C1.00166 4.86513 1.01215 4.87698 1.02348 4.88831L3.09807 6.96287C3.25053 7.11537 3.49796 7.11527 3.65036 6.96287L8.60528 2.00795C8.74649 1.86675 8.75695 1.64433 8.6367 1.49107C8.7569 1.64433 8.74643 1.86671 8.60524 2.00789L3.65032 6.96281C3.49792 7.11521 3.25048 7.11532 3.09803 6.96281L1.02343 4.88825C1.01212 4.87694 1.00165 4.86511 0.992017 4.85283Z" fill="white"></path></svg></span> 20+ Premium Modules</li><li class="wpuf-flex wpuf-items-center"><span class="tooltip-check"><svg width="10" height="8" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8.92671 1.13426C8.59667 0.804188 8.06162 0.804234 7.73159 1.13423L3.37421 5.49165L1.89718 4.01462C1.56712 3.68454 1.03208 3.68467 0.702082 4.0146C0.372021 4.34463 0.372046 4.8797 0.702068 5.20972L2.77666 7.28428C3.10675 7.61442 3.64199 7.61406 3.97177 7.28428L8.92668 2.32937C9.25676 1.99933 9.25668 1.46426 8.92671 1.13426ZM0.992017 4.85283C1.00166 4.86513 1.01215 4.87698 1.02348 4.88831L3.09807 6.96287C3.25053 7.11537 3.49796 7.11527 3.65036 6.96287L8.60528 2.00795C8.74649 1.86675 8.75695 1.64433 8.6367 1.49107C8.7569 1.64433 8.74643 1.86671 8.60524 2.00789L3.65032 6.96281C3.49792 7.11521 3.25048 7.11532 3.09803 6.96281L1.02343 4.88825C1.01212 4.87694 1.00165 4.86511 0.992017 4.85283Z" fill="white"></path></svg></span> User Activity and Reports</li><li class="wpuf-flex wpuf-items-center"><span class="tooltip-check"><svg width="10" height="8" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8.92671 1.13426C8.59667 0.804188 8.06162 0.804234 7.73159 1.13423L3.37421 5.49165L1.89718 4.01462C1.56712 3.68454 1.03208 3.68467 0.702082 4.0146C0.372021 4.34463 0.372046 4.8797 0.702068 5.20972L2.77666 7.28428C3.10675 7.61442 3.64199 7.61406 3.97177 7.28428L8.92668 2.32937C9.25676 1.99933 9.25668 1.46426 8.92671 1.13426ZM0.992017 4.85283C1.00166 4.86513 1.01215 4.87698 1.02348 4.88831L3.09807 6.96287C3.25053 7.11537 3.49796 7.11527 3.65036 6.96287L8.60528 2.00795C8.74649 1.86675 8.75695 1.64433 8.6367 1.49107C8.7569 1.64433 8.74643 1.86671 8.60524 2.00789L3.65032 6.96281C3.49792 7.11521 3.25048 7.11532 3.09803 6.96281L1.02343 4.88825C1.01212 4.87694 1.00165 4.86511 0.992017 4.85283Z" fill="white"></path></svg></span> Private Messaging Option</li><li class="wpuf-flex wpuf-items-center"><span class="tooltip-check"><svg width="10" height="8" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8.92671 1.13426C8.59667 0.804188 8.06162 0.804234 7.73159 1.13423L3.37421 5.49165L1.89718 4.01462C1.56712 3.68454 1.03208 3.68467 0.702082 4.0146C0.372021 4.34463 0.372046 4.8797 0.702068 5.20972L2.77666 7.28428C3.10675 7.61442 3.64199 7.61406 3.97177 7.28428L8.92668 2.32937C9.25676 1.99933 9.25668 1.46426 8.92671 1.13426ZM0.992017 4.85283C1.00166 4.86513 1.01215 4.87698 1.02348 4.88831L3.09807 6.96287C3.25053 7.11537 3.49796 7.11527 3.65036 6.96287L8.60528 2.00795C8.74649 1.86675 8.75695 1.64433 8.6367 1.49107C8.7569 1.64433 8.74643 1.86671 8.60524 2.00789L3.65032 6.96281C3.49792 7.11521 3.25048 7.11532 3.09803 6.96281L1.02343 4.88825C1.01212 4.87694 1.00165 4.86511 0.992017 4.85283Z" fill="white"></path></svg></span> License for 20 websites</li></ul><div class="pro-link"><a href="https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=wpdashboard&amp;utm_medium=popup" target="_blank" class="wpuf-button button-upgrade-to-pro wpuf-flex wpuf-items-center wpuf-w-[calc(100%-2rem)] wpuf-justify-around">Upgrade to PRO<span class="pro-icon icon-white"><svg width="20" height="15" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19.2131 4.11564C19.2161 4.16916 19.2121 4.22364 19.1983 4.27775L17.9646 10.5323C17.9024 10.7741 17.6796 10.9441 17.4235 10.9455L10.0216 10.9818H10.0188H2.61682C2.35933 10.9818 2.13495 10.8112 2.07275 10.5681L0.839103 4.29542C0.824897 4.23985 0.820785 4.18385 0.824374 4.12895C0.34714 3.98269 0 3.54829 0 3.03636C0 2.40473 0.528224 1.89091 1.17757 1.89091C1.82692 1.89091 2.35514 2.40473 2.35514 3.03636C2.35514 3.39207 2.18759 3.71033 1.92523 3.92058L3.46976 5.43433C3.86011 5.81695 4.40179 6.03629 4.95596 6.03629C5.61122 6.03629 6.23596 5.7336 6.62938 5.22647L9.1677 1.95491C8.95447 1.74764 8.82243 1.46124 8.82243 1.14545C8.82243 0.513818 9.35065 0 10 0C10.6493 0 11.1776 0.513818 11.1776 1.14545C11.1776 1.45178 11.0526 1.72982 10.8505 1.93556L10.8526 1.93811L13.3726 5.21869C13.7658 5.73069 14.3928 6.03636 15.0499 6.03636C15.6092 6.03636 16.1351 5.82451 16.5305 5.43978L18.0848 3.92793C17.8169 3.71775 17.6449 3.39644 17.6449 3.03636C17.6449 2.40473 18.1731 1.89091 18.8224 1.89091C19.4718 1.89091 20 2.40473 20 3.03636C20 3.53462 19.6707 3.9584 19.2131 4.11564ZM17.8443 12.6909C17.8443 12.3897 17.5932 12.1455 17.2835 12.1455H2.77884C2.46916 12.1455 2.21809 12.3897 2.21809 12.6909V14C2.21809 14.3012 2.46916 14.5455 2.77884 14.5455H17.2835C17.5932 14.5455 17.8443 14.3012 17.8443 14V12.6909Z" fill="#FB9A28"></path></svg></span></a></div><i style="left:50%;top:100%;transform:initial;"></i>', 4)]));
}
const Vp = Cs(c2, [["render", p2]]), f2 = { key: 0, class: "wpuf-block wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600 wpuf-flex wpuf-items-center" }, v2 = ["for", "innerHTML"], m2 = ["data-tip"], h2 = { class: "pro-icon-title wpuf-relative wpuf-pt-1 wpuf-group" }, g2 = { class: "wpuf-w-full wpuf-col-span-2 wpuf-relative wpuf-group" }, w2 = { key: 0, class: "wpuf-hidden wpuf-rounded-md group-hover:wpuf-flex group-hover:wpuf-cursor-pointer wpuf-absolute wpuf-items-center wpuf-justify-center wpuf-bg-black/25 wpuf-z-10 wpuf-p-4 wpuf-w-[104%] wpuf-h-[180%] wpuf-top-[-40%] wpuf-left-[-2%]" }, y2 = { href: "https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=wpdashboard&utm_medium=popup", target: "_blank", class: "wpuf-inline-flex wpuf-align-center wpuf-p-2 wpuf-bg-amber-600 wpuf-text-white hover:wpuf-text-white wpuf-rounded-md" }, b2 = ["value", "name", "id", "placeholder"], _2 = ["value", "name", "id", "placeholder"], x2 = ["name", "id", "placeholder"], k2 = ["value", "name", "id"], S2 = ["name", "id"], C2 = ["value", "selected"], M2 = { key: 8, class: "label" }, T2 = { class: "label-text-alt" }, A2 = { key: 9, class: "label" }, D2 = { class: "label-text-alt wpuf-text-red-500" }, jp = { __name: "SectionInputField", props: { field: Object, fieldId: String, isChildField: { type: Boolean, default: false } }, emits: ["toggleDependentFields"], setup(e, { emit: t }) {
  const n = t, a = Yt();
  Hn("subSection");
  const r = e, l = xo(), o = a.currentSubscription;
  Ya(a.errors);
  const { field: i, fieldId: s, isChildField: c } = Pt(r), d = te(/* @__PURE__ */ new Date()), u = J(() => i.value.is_pro && !wpufSubscriptions.isProActive), p = () => {
    switch (i.value.db_type) {
      case "meta":
        return a.getMetaValue(i.value.db_key);
      case "meta_serialized":
        return a.getSerializedMetaValue(i.value.db_key, i.value.serialize_key);
      default:
        return o.hasOwnProperty(i.value.db_key) ? o[i.value.db_key] : "";
    }
  }, v = J(() => {
    const P = p(i.value.db_type, i.value.db_key);
    return b(i.value.type, P);
  }), b = (P, $) => {
    switch (P) {
      case "switcher":
        return $ === "on" || $ === "yes" || $ === "private";
      case "time-date":
        return new Date($);
      case "inline":
        return "";
      case "multi-select":
        return Array.isArray($) ? $ : [];
      default:
        return $;
    }
  }, h = (P) => {
    d.value = P, i.value.db_type === "post" ? a.modifyCurrentSubscription(i.value.db_key, P) : a.setMetaValue(i.value.db_key, P);
  }, N = te(v), I = () => {
    i.value.db_key === "post_status" ? a.modifyCurrentSubscription(i.value.db_key, N.value ? "publish" : "private") : a.setMetaValue(i.value.db_key, N.value ? "off" : "on");
  }, x = J(() => !l.hiddenFields.includes(s.value)), _ = (P) => {
    switch (i.value.db_type) {
      case "meta_serialized":
        a.modifyCurrentSubscription(i.value.db_key, P.target.value, i.value.serialize_key);
        break;
      case "post":
        a.modifyCurrentSubscription(i.value.db_key, P.target.value);
        break;
      default:
        a.setMetaValue(i.value.db_key, P.target.value);
    }
  }, g = (P) => {
    i.value.db_key === "post_title" && a.modifyCurrentSubscription("post_name", P.target.value.replace(/\s+/g, "-").toLowerCase());
  }, R = (P) => {
    !["Backspace", "Delete", "Tab", "ArrowLeft", "ArrowRight", "."].includes(P.key) && isNaN(Number(P.key)) && P.preventDefault();
  }, M = J(() => wpufSubscriptions.fields.advanced_configuration.hasOwnProperty("taxonomy_restriction") ? wpufSubscriptions.fields.advanced_configuration.taxonomy_restriction[i.value.id].term_fields : []), C = (P) => {
    const $ = Fe(a.taxonomyRestriction);
    $[s.value] = P, a.$patch({ taxonomyRestriction: $ });
  }, Y = J(() => {
    const P = ["wpuf-gap-4"];
    return i.value.label ? P.push("wpuf-grid wpuf-grid-cols-3 wpuf-p-4") : P.push("wpuf-py-4 wpuf-pl-3 wpuf-pr-4"), c.value && P.push("wpuf-col-span-2 wpuf-w-1/2"), P;
  });
  return ot(() => {
    i.value.type === "switcher" && n("toggleDependentFields", s.value, N.value);
  }), ot(() => {
    if (i.value.type !== "multi-select") return;
    const P = wpufSubscriptions.fields.advanced_configuration.taxonomy_restriction[i.value.id].term_fields.map((z) => z.value);
    let $ = [];
    v.value.map((z) => {
      P.includes(z) && $.push(z);
    });
    const H = Fe(a.taxonomyRestriction);
    H[s.value] = $, a.$patch({ taxonomyRestriction: H });
  }), (P, $) => zn((T(), F("div", { class: pe(Y.value) }, [f(i).label ? (T(), F("div", f2, [L("label", { for: f(i).name, innerHTML: f(i).label }, null, 8, v2), f(i).tooltip ? (T(), F("span", { key: 0, class: "wpuf-tooltip before:wpuf-bg-gray-700 before:wpuf-text-zinc-50 after:wpuf-border-t-gray-700 after:wpuf-border-x-transparent wpuf-cursor-pointer wpuf-ml-2 wpuf-z-10", "data-tip": f(i).tooltip }, $[7] || ($[7] = [L("svg", { xmlns: "http://www.w3.org/2000/svg", width: "18", height: "18", fill: "none" }, [L("path", { d: "M9.833 12.333H9V9h-.833M9 5.667h.008M16.5 9a7.5 7.5 0 1 1-15 0 7.5 7.5 0 1 1 15 0z", stroke: "#9CA3AF", "stroke-width": "2", "stroke-linecap": "round", "stroke-linejoin": "round" })], -1)]), 8, m2)) : Z("", true), $[8] || ($[8] = Ge("    ")), L("span", h2, [u.value ? (T(), Pe(Ip, { key: 0 })) : Z("", true), Ne(Vp)])])) : Z("", true), L("div", g2, [u.value ? (T(), F("div", w2, [L("a", y2, [Ge(ge(f(ke)("Upgrade to Pro", "wp-user-frontend")) + " ", 1), $[9] || ($[9] = L("span", { class: "pro-icon icon-white" }, [L("svg", { width: "20", height: "15", viewBox: "0 0 20 15", fill: "none", xmlns: "http://www.w3.org/2000/svg" }, [L("path", { d: "M19.2131 4.11564C19.2161 4.16916 19.2121 4.22364 19.1983 4.27775L17.9646 10.5323C17.9024 10.7741 17.6796 10.9441 17.4235 10.9455L10.0216 10.9818H10.0188H2.61682C2.35933 10.9818 2.13495 10.8112 2.07275 10.5681L0.839103 4.29542C0.824897 4.23985 0.820785 4.18385 0.824374 4.12895C0.34714 3.98269 0 3.54829 0 3.03636C0 2.40473 0.528224 1.89091 1.17757 1.89091C1.82692 1.89091 2.35514 2.40473 2.35514 3.03636C2.35514 3.39207 2.18759 3.71033 1.92523 3.92058L3.46976 5.43433C3.86011 5.81695 4.40179 6.03629 4.95596 6.03629C5.61122 6.03629 6.23596 5.7336 6.62938 5.22647L9.1677 1.95491C8.95447 1.74764 8.82243 1.46124 8.82243 1.14545C8.82243 0.513818 9.35065 0 10 0C10.6493 0 11.1776 0.513818 11.1776 1.14545C11.1776 1.45178 11.0526 1.72982 10.8505 1.93556L10.8526 1.93811L13.3726 5.21869C13.7658 5.73069 14.3928 6.03636 15.0499 6.03636C15.6092 6.03636 16.1351 5.82451 16.5305 5.43978L18.0848 3.92793C17.8169 3.71775 17.6449 3.39644 17.6449 3.03636C17.6449 2.40473 18.1731 1.89091 18.8224 1.89091C19.4718 1.89091 20 2.40473 20 3.03636C20 3.53462 19.6707 3.9584 19.2131 4.11564ZM17.8443 12.6909C17.8443 12.3897 17.5932 12.1455 17.2835 12.1455H2.77884C2.46916 12.1455 2.21809 12.3897 2.21809 12.6909V14C2.21809 14.3012 2.46916 14.5455 2.77884 14.5455H17.2835C17.5932 14.5455 17.8443 14.3012 17.8443 14V12.6909Z", fill: "#FB9A28" })])], -1))])])) : Z("", true), f(i).type === "input-text" ? (T(), F("input", { key: 1, type: "text", value: v.value, name: f(i).name, id: f(i).name, placeholder: f(i).placeholder ? f(i).placeholder : "", onInput: $[0] || ($[0] = (H) => [_(H), g(H)]), class: pe([f(a).errors[f(s)] ? "!wpuf-border-red-500" : "!wpuf-border-gray-300", "placeholder:wpuf-text-gray-400 wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm"]) }, null, 42, b2)) : Z("", true), f(i).type === "input-number" ? (T(), F("input", { key: 2, type: "number", value: v.value, name: f(i).name, id: f(i).name, placeholder: f(i).placeholder ? f(i).placeholder : "", onInput: $[1] || ($[1] = (H) => [_(H), g(H)]), onKeydown: R, min: "-1", class: pe([f(a).errors[f(s)] ? "!wpuf-border-red-500" : "!wpuf-border-gray-300", "placeholder:wpuf-text-gray-400 wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm"]) }, null, 42, _2)) : Z("", true), f(i).type === "textarea" ? (T(), F("textarea", { key: 3, name: f(i).name, id: f(i).name, placeholder: f(i).placeholder ? f(i).placeholder : "", rows: "3", onInput: $[2] || ($[2] = (H) => [_(H), g(H)]), class: pe([f(a).errors[f(s)] ? "!wpuf-border-red-500" : "!wpuf-border-gray-300", "placeholder:wpuf-text-gray-400 wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm"]) }, ge(v.value), 43, x2)) : Z("", true), f(i).type === "switcher" ? (T(), F("button", { key: 4, onClick: $[3] || ($[3] = (H) => [I(), P.$emit("toggleDependentFields", f(s), N.value)]), type: "button", value: v.value, name: f(i).name, id: f(i).name, class: pe([N.value ? "wpuf-bg-indigo-600" : "wpuf-bg-gray-200", "placeholder:wpuf-text-gray-400 wpuf-bg-gray-200 wpuf-relative wpuf-inline-flex wpuf-h-6 wpuf-w-11 wpuf-flex-shrink-0 wpuf-cursor-pointer wpuf-rounded-full wpuf-border-2 wpuf-border-transparent wpuf-transition-colors wpuf-duration-200 wpuf-ease-in-out"]), role: "switch" }, [L("span", { "aria-hidden": "true", class: pe([N.value ? "wpuf-translate-x-5" : "wpuf-translate-x-0", "wpuf-translate-x-0 wpuf-pointer-events-none wpuf-inline-block wpuf-h-5 wpuf-w-5 wpuf-transform wpuf-rounded-full wpuf-bg-white wpuf-shadow wpuf-ring-0 wpuf-transition wpuf-duration-200 wpuf-ease-in-out"]) }, null, 2)], 10, k2)) : Z("", true), f(i).type === "time-date" ? (T(), Pe(f(wo), { key: 5, textInput: "", modelValue: d.value, "onUpdate:modelValue": [$[4] || ($[4] = (H) => d.value = H), h], name: f(i).name, uid: f(i).name, "enable-seconds": "" }, null, 8, ["modelValue", "name", "uid"])) : Z("", true), f(i).type === "select" ? (T(), F("select", { key: 6, name: f(i).name, id: f(i).name, class: pe([f(a).errors[f(s)] ? "!wpuf-border-red-500" : "!wpuf-border-gray-300", "wpuf-w-full !wpuf-max-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm"]), onInput: $[5] || ($[5] = (H) => [_(H), g(H)]) }, [(T(true), F(Ce, null, Ve(f(i).options, (H, z) => (T(), F("option", { value: z, selected: z === v.value, key: z }, ge(H), 9, C2))), 128))], 42, S2)) : Z("", true), f(i).type === "multi-select" ? (T(), Pe(f(Ss), { key: 7, id: f(i).id, name: f(i).name, placeholder: f(i).placeholder ? f(i).placeholder : f(ke)("Select options", "wp-user-frontend"), modelValue: v.value, "onUpdate:modelValue": $[6] || ($[6] = (H) => v.value = H), options: M.value, mode: "tags", onInput: C, "close-on-select": false, classes: { container: "wpuf-w-full wpuf-border wpuf-rounded-md !wpuf-border-gray-300 wpuf-bg-white wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm", wrapper: "wpuf-min-h-max wpuf-align-center wpuf-cursor-pointer wpuf-flex wpuf-justify-end wpuf-w-full wpuf-relative", placeholder: "wpuf-ml-2 wpuf-flex wpuf-items-center wpuf-h-full wpuf-absolute wpuf-left-0 wpuf-top-0 wpuf-pointer-events-none wpuf-bg-transparent wpuf-form-color-placeholder rtl:wpuf-left-auto rtl:wpuf-right-0 rtl:wpuf-pl-0 wpuf-form-pl-input rtl:wpuf-form-pr-input", tags: "wpuf-h-max wpuf-flex-grow wpuf-flex-shrink wpuf-flex wpuf-flex-wrap wpuf-items-center wpuf-pl-1 wpuf-pt-1 wpuf-min-w-0 rtl:wpuf-pl-0 rtl:wpuf-pr-2", tag: "wpuf-bg-indigo-600 wpuf-text-white wpuf-text-sm wpuf-font-semibold wpuf-py-0.5 wpuf-pl-2 wpuf-rounded wpuf-mr-1 wpuf-mb-1 wpuf-flex wpuf-items-center wpuf-whitespace-nowrap wpuf-min-w-0 rtl:wpuf-pl-0 rtl:wpuf-pr-2 rtl:wpuf-mr-0 rtl:wpuf-ml-1", clear: "wpuf-mt-1 wpuf-pr-2" } }, null, 8, ["id", "name", "placeholder", "modelValue", "options"])) : Z("", true), f(i).description ? (T(), F("div", M2, [L("span", T2, ge(f(i).description), 1)])) : Z("", true), f(a).errors[f(s)] ? (T(), F("div", A2, [L("span", D2, ge(f(a).errors[f(s)].message), 1)])) : Z("", true)])], 2)), [[ca, x.value]]);
} }, L2 = { class: "wpuf-grid wpuf-grid-cols-3 wpuf-p-4 wpuf-gap-4" }, O2 = { class: "wpuf-block wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600 wpuf-flex wpuf-items-center" }, P2 = ["for", "innerHTML"], $2 = ["data-tip"], R2 = { class: "wpuf--ml-3 wpuf-flex wpuf-justify-between wpuf-col-span-2 wpuf--mr-3" }, E2 = { __name: "SectionInnerField", props: { parentField: Object, fieldId: String }, setup(e) {
  const t = e, { parentField: n, fieldId: a } = Pt(t), r = xo(), l = J(() => !r.hiddenFields.includes(a.value));
  return (o, i) => zn((T(), F("div", L2, [L("div", O2, [L("label", { for: f(n).name, innerHTML: f(n).label }, null, 8, P2), f(n).tooltip ? (T(), F("div", { key: 0, class: "wpuf-tooltip before:wpuf-bg-gray-700 before:wpuf-text-zinc-50 after:wpuf-border-t-gray-700 after:wpuf-border-x-transparent wpuf-cursor-pointer wpuf-ml-2 wpuf-z-10", "data-tip": f(n).tooltip }, i[0] || (i[0] = [L("svg", { xmlns: "http://www.w3.org/2000/svg", width: "18", height: "18", fill: "none" }, [L("path", { d: "M9.833 12.333H9V9h-.833M9 5.667h.008M16.5 9a7.5 7.5 0 1 1-15 0 7.5 7.5 0 1 1 15 0z", stroke: "#9CA3AF", "stroke-width": "2", "stroke-linecap": "round", "stroke-linejoin": "round" })], -1)]), 8, $2)) : Z("", true)]), L("div", R2, [(T(true), F(Ce, null, Ve(f(n).fields, (s) => (T(), Pe(jp, { field: s, fieldId: s.id, isChildField: true }, null, 8, ["field", "fieldId"]))), 256))])], 512)), [[ca, l.value]]);
} }, N2 = { class: "wpuf-border wpuf-border-gray-200 wpuf-rounded-xl wpuf-rounded-b-xl wpuf-mt-4 wpuf-mb-4" }, I2 = { class: "wpuf-m-0" }, V2 = { class: "wpuf-flex" }, j2 = { key: 0, class: "wpuf-relative wpuf-m-0 wpuf-p-0 wpuf-ml-2 wpuf-mt-[1px] wpuf-italic wpuf-text-[11px] wpuf-text-gray-400" }, B2 = { class: "pro-icon-title wpuf-relative wpuf-pt-1 wpuf-group wpuf-ml-2" }, F2 = { key: 0, class: "wpuf-rounded-b-xl wpuf-bg-yellow-50 wpuf-p-4" }, Y2 = { class: "wpuf-flex wpuf-items-center" }, q2 = { class: "wpuf-ml-3" }, z2 = { class: "wpuf-mt-2 wpuf-text-sm wpuf-text-yellow-700" }, H2 = ["innerHTML"], K2 = { __name: "Subsection", props: { subSection: Object, subscription: Object, fields: Object }, setup(e) {
  const t = e, { subSection: n, subscription: a, fields: r } = Pt(t), l = Hn("wpufSubscriptions"), o = xo();
  lo("subSection", n.value.id), te(true);
  const i = te(false), s = ["overview", "content_limit", "payment_details"];
  i.value = !s.includes(n.value.id);
  const c = (d, u) => {
    if (!l.dependentFields.hasOwnProperty(d)) return;
    o.modifierFieldStatus[d] = u;
    let p = [];
    for (const v in o.modifierFieldStatus) for (const b in l.dependentFields[v]) o.modifierFieldStatus[v] ? p = p.filter((h) => h !== b) : p.push(b);
    o.hiddenFields = p;
  };
  return (d, u) => (T(), F("div", N2, [L("h2", I2, [L("button", { type: "button", onClick: u[0] || (u[0] = (p) => i.value = !i.value), class: pe([i.value ? "wpuf-rounded-xl" : "wpuf-rounded-t-xl", "wpuf-flex wpuf-items-center wpuf-justify-between wpuf-w-full wpuf-p-4 wpuf-font-medium rtl:wpuf-text-right wpuf-text-gray-500 wpuf-bg-gray-100 wpuf-gap-3"]) }, [L("span", V2, [Ge(ge(f(n).label) + " ", 1), f(n).sub_label ? (T(), F("span", j2, ge(f(n).sub_label), 1)) : Z("", true), L("span", B2, [f(n).is_pro ? (T(), Pe(Ip, { key: 0 })) : Z("", true), Ne(Vp)])]), (T(), F("svg", { class: pe([i.value ? "wpuf-rotate-90" : "wpuf-rotate-180", "wpuf-w-3 wpuf-h-3 shrink-0"]), "data-accordion-icon": "", "aria-hidden": "true", xmlns: "http://www.w3.org/2000/svg", fill: "none", viewBox: "0 0 10 6" }, u[1] || (u[1] = [L("path", { stroke: "currentColor", "stroke-linecap": "round", "stroke-linejoin": "round", "stroke-width": "2", d: "M9 5 5 1 1 5" }, null, -1)]), 2))], 2)]), (T(true), F(Ce, null, Ve(f(r), (p, v) => zn((T(), F("div", null, [p.type !== "inline" ? (T(), Pe(jp, { key: 0, onToggleDependentFields: c, field: p, fieldId: v, serializeKey: p.serialize_key, subscription: f(a) }, null, 8, ["field", "fieldId", "serializeKey", "subscription"])) : (T(), Pe(E2, { key: 1, parentField: p, fieldId: v, subscription: f(a) }, null, 8, ["parentField", "fieldId", "subscription"]))], 512)), [[ca, !i.value]])), 256)), !i.value && f(n).notice ? (T(), F("div", F2, [L("div", Y2, [u[2] || (u[2] = L("div", { class: "wpuf-flex-shrink-0" }, [L("svg", { class: "wpuf-h-5 wpuf-w-5 wpuf-text-yellow-400", viewBox: "0 0 20 20", fill: "currentColor", "aria-hidden": "true" }, [L("path", { "fill-rule": "evenodd", d: "M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z", "clip-rule": "evenodd" })])], -1)), L("div", q2, [L("div", z2, [L("p", { innerHTML: f(n).notice.message }, null, 8, H2)])])])])) : Z("", true)]));
} }, Z2 = { class: "wpuf-mt-4 wpuf-text-sm wpuf-font-medium wpuf-text-center wpuf-text-gray-500 wpuf-border-b wpuf-border-gray-200" }, W2 = { class: "wpuf-flex wpuf-flex-wrap wpuf--mb-px" }, U2 = ["onClick"], Bp = { __name: "SubscriptionsDetails", setup(e) {
  const t = Yt(), n = xo(), a = t.currentSubscription, r = te("subscription_details"), l = Hn("wpufSubscriptions");
  un({ planName: false, date: false, isPrivate: false }), lo("currentSection", r);
  const o = () => {
    for (const i in l.dependentFields) for (const s in l.dependentFields[i]) n.modifierFields.hasOwnProperty(i) ? n.modifierFields[i][s] = l.dependentFields[i][s] : n.modifierFields[i] = { [s]: l.dependentFields[i][s] };
  };
  return yr(() => {
    o();
  }), (i, s) => (T(), F(Ce, null, [L("div", Z2, [L("ul", W2, [(T(true), F(Ce, null, Ve(f(l).sections, (c) => (T(), F("li", { key: c.id, class: "wpuf-mb-0 wpuf-me-2" }, [L("button", { onClick: (d) => r.value = c.id, class: pe([r.value === c.id ? "wpuf-border-b-2 wpuf-border-blue-600 wpuf-text-blue-600" : "", "active:wpuf-shadow-none focus:wpuf-shadow-none wpuf-inline-block wpuf-p-4 wpuf-rounded-t-lg hover:wpuf-text-blue-600 hover:wpuf-border-b-2 hover:wpuf-border-blue-600"]) }, ge(c.title), 11, U2)]))), 128))])]), (T(true), F(Ce, null, Ve(f(l).subSections, (c, d) => (T(), F(Ce, null, [(T(true), F(Ce, null, Ve(c, (u) => zn((T(), Pe(K2, { key: u.id, currentSection: r.value, subSection: u, subscription: f(a), fields: f(l).fields[d][u.id] }, null, 8, ["currentSection", "subSection", "subscription", "fields"])), [[ca, r.value === d]])), 128))], 64))), 256))], 64));
} }, G2 = { class: "wpuf-relative" }, Q2 = ["disabled"], X2 = { class: "wpuf-hidden hover:wpuf-block peer-hover:wpuf-block wpuf-cursor-pointer wpuf-w-44 wpuf-z-40 wpuf-bg-white wpuf-border border-[#DBDBDB] wpuf-absolute wpuf-z-10 wpuf-shadow wpuf-right-0 wpuf-rounded-md after:content-[''] before:content-[''] after:wpuf-absolute before:wpuf-absolute after:w-[13px] before:w-[70%] before:-right-[1px] after:h-[13px] before:wpuf-h-3 before:wpuf-mt-3 after:top-[-7px] before:wpuf--top-6 after:right-[1.4rem] after:z-[-1] after:wpuf-bg-white after:wpuf-border after:border-[#DBDBDB] after:!rotate-45 after:wpuf-border-r-0 after:wpuf-border-b-0" }, Ms = { __name: "UpdateButton", props: { buttonText: { type: String, default: ke("Update", "wp-user-frontend") } }, setup(e) {
  const t = e, n = Yt(), a = te(t.buttonText);
  return (r, l) => (T(), F("div", G2, [L("button", { disabled: f(n).isUpdating, class: pe([f(n).isUpdating ? "wpuf-cursor-not-allowed wpuf-bg-gray-50" : "", "wpuf-peer wpuf-inline-flex wpuf-justify-between wpuf-items-center wpuf-cursor-pointer wpuf-bg-indigo-600 hover:wpuf-bg-indigo-800 wpuf-text-white wpuf-font-medium wpuf-text-base wpuf-py-2 wpuf-px-5 wpuf-rounded-md min-w-[122px]"]) }, [Ge(ge(a.value) + " ", 1), l[2] || (l[2] = L("svg", { class: "wpuf-rotate-180 wpuf-w-3 wpuf-h-3 shrink-0 wpuf-ml-4", "data-accordion-icon": "", "aria-hidden": "true", xmlns: "http://www.w3.org/2000/svg", fill: "none", viewBox: "0 0 10 6" }, [L("path", { stroke: "currentColor", "stroke-linecap": "round", "stroke-linejoin": "round", "stroke-width": "2", d: "M9 5 5 1 1 5" })], -1))], 10, Q2), L("div", X2, [L("span", { onClick: l[0] || (l[0] = () => {
    f(n).currentSubscription.post_status = "publish", r.$emit("updateSubscription");
  }), class: pe([f(n).isUpdating ? "wpuf-cursor-not-allowed wpuf-bg-gray-50" : "", "wpuf-flex wpuf-py-3 wpuf-items-center wpuf-px-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-bg-indigo-700 hover:wpuf-text-white wpuf-rounded-t-md"]) }, ge(f(ke)("Publish", "wp-user-frontend")), 3), L("span", { onClick: l[1] || (l[1] = () => {
    f(n).currentSubscription.post_status = "draft", r.$emit("updateSubscription");
  }), class: pe([f(n).isUpdating ? "wpuf-cursor-not-allowed wpuf-bg-gray-50" : "", "wpuf-flex wpuf-py-3 wpuf-items-center wpuf-px-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-bg-indigo-700 hover:wpuf-text-white wpuf-rounded-b-md"]) }, ge(f(ke)("Save as Draft", "wp-user-frontend")), 3)])]));
} }, pl = sl("notice", { state: () => ({ display: false, notices: [] }), actions: { addNotice(e) {
  this.notices.push(e);
}, removeNotice(e) {
  this.notices.splice(e, 1);
} } }), ac = Object.freeze({}), Fp = Object.assign, Yp = Array.isArray, ko = (e) => typeof e == "function", J2 = (e) => typeof e == "string", e_ = (e) => typeof e == "symbol", ti = (e) => e !== null && typeof e == "object";
let rc;
const t_ = () => rc || (rc = typeof globalThis < "u" ? globalThis : typeof self < "u" ? self : typeof window < "u" ? window : typeof global < "u" ? global : {});
new Set(Object.getOwnPropertyNames(Symbol).filter((e) => e !== "arguments" && e !== "caller").map((e) => Symbol[e]).filter(e_));
function qp(e) {
  return Di(e) ? qp(e.__v_raw) : !!(e && e.__v_isReactive);
}
function Di(e) {
  return !!(e && e.__v_isReadonly);
}
function ni(e) {
  return !!(e && e.__v_isShallow);
}
function lr(e) {
  const t = e && e.__v_raw;
  return t ? lr(t) : e;
}
function zp(e) {
  return !!(e && e.__v_isRef === true);
}
const Ba = [];
function n_(e) {
  Ba.push(e);
}
function a_() {
  Ba.pop();
}
function r_(e, ...t) {
  const n = Ba.length ? Ba[Ba.length - 1].component : null, a = n && n.appContext.config.warnHandler, r = l_();
  if (a) Ts(a, n, 11, [e + t.join(""), n && n.proxy, r.map(({ vnode: l }) => `at <${Xp(n, l.type)}>`).join(`
`), r]);
  else {
    const l = [`[Vue warn]: ${e}`, ...t];
    r.length && l.push(`
`, ...o_(r)), console.warn(...l);
  }
}
function l_() {
  let e = Ba[Ba.length - 1];
  if (!e) return [];
  const t = [];
  for (; e; ) {
    const n = t[0];
    n && n.vnode === e ? n.recurseCount++ : t.push({ vnode: e, recurseCount: 0 });
    const a = e.component && e.component.parent;
    e = a && a.vnode;
  }
  return t;
}
function o_(e) {
  const t = [];
  return e.forEach((n, a) => {
    t.push(...a === 0 ? [] : [`
`], ...i_(n));
  }), t;
}
function i_({ vnode: e, recurseCount: t }) {
  const n = t > 0 ? `... (${t} recursive calls)` : "", a = e.component ? e.component.parent == null : false, r = ` at <${Xp(e.component, e.type, a)}`, l = ">" + n;
  return e.props ? [r, ...s_(e.props), l] : [r + l];
}
function s_(e) {
  const t = [], n = Object.keys(e);
  return n.slice(0, 3).forEach((a) => {
    t.push(...Hp(a, e[a]));
  }), n.length > 3 && t.push(" ..."), t;
}
function Hp(e, t, n) {
  return J2(t) ? (t = JSON.stringify(t), n ? t : [`${e}=${t}`]) : typeof t == "number" || typeof t == "boolean" || t == null ? n ? t : [`${e}=${t}`] : zp(t) ? (t = Hp(e, lr(t.value), true), n ? t : [`${e}=Ref<`, t, ">"]) : ko(t) ? [`${e}=fn${t.name ? `<${t.name}>` : ""}`] : (t = lr(t), n ? t : [`${e}=`, t]);
}
const Kp = { sp: "serverPrefetch hook", bc: "beforeCreate hook", c: "created hook", bm: "beforeMount hook", m: "mounted hook", bu: "beforeUpdate hook", u: "updated", bum: "beforeUnmount hook", um: "unmounted hook", a: "activated hook", da: "deactivated hook", ec: "errorCaptured hook", rtc: "renderTracked hook", rtg: "renderTriggered hook", 0: "setup function", 1: "render function", 2: "watcher getter", 3: "watcher callback", 4: "watcher cleanup function", 5: "native event handler", 6: "component event handler", 7: "vnode hook", 8: "directive hook", 9: "transition hook", 10: "app errorHandler", 11: "app warnHandler", 12: "ref function", 13: "async component loader", 14: "scheduler flush. This is likely a Vue internals bug. Please open an issue at https://github.com/vuejs/core ." };
function Ts(e, t, n, a) {
  try {
    return a ? e(...a) : e();
  } catch (r) {
    Zp(r, t, n);
  }
}
function Zp(e, t, n, a = true) {
  const r = t ? t.vnode : null;
  if (t) {
    let l = t.parent;
    const o = t.proxy, i = Kp[n];
    for (; l; ) {
      const c = l.ec;
      if (c) {
        for (let d = 0; d < c.length; d++) if (c[d](e, o, i) === false) return;
      }
      l = l.parent;
    }
    const s = t.appContext.config.errorHandler;
    if (s) {
      Ts(s, null, 10, [e, o, i]);
      return;
    }
  }
  u_(e, n, r, a);
}
function u_(e, t, n, a = true) {
  {
    const r = Kp[t];
    if (n && n_(n), r_(`Unhandled error${r ? ` during execution of ${r}` : ""}`), n && a_(), a) throw e;
    console.error(e);
  }
}
let Zl = false, Li = false;
const vn = [];
let sa = 0;
const or = [];
let Vn = null, oa = 0;
const c_ = Promise.resolve(), d_ = 100;
function p_(e) {
  let t = sa + 1, n = vn.length;
  for (; t < n; ) {
    const a = t + n >>> 1, r = vn[a], l = al(r);
    l < e || l === e && r.pre ? t = a + 1 : n = a;
  }
  return t;
}
function f_(e) {
  (!vn.length || !vn.includes(e, Zl && e.allowRecurse ? sa + 1 : sa)) && (e.id == null ? vn.push(e) : vn.splice(p_(e.id), 0, e), Wp());
}
function Wp() {
  !Zl && !Li && (Li = true, c_.then(Up));
}
function v_(e) {
  Yp(e) ? or.push(...e) : (!Vn || !Vn.includes(e, e.allowRecurse ? oa + 1 : oa)) && or.push(e), Wp();
}
function m_(e) {
  if (or.length) {
    const t = [...new Set(or)].sort((n, a) => al(n) - al(a));
    if (or.length = 0, Vn) {
      Vn.push(...t);
      return;
    }
    for (Vn = t, e = e || /* @__PURE__ */ new Map(), oa = 0; oa < Vn.length; oa++) Gp(e, Vn[oa]) || Vn[oa]();
    Vn = null, oa = 0;
  }
}
const al = (e) => e.id == null ? 1 / 0 : e.id, h_ = (e, t) => {
  const n = al(e) - al(t);
  if (n === 0) {
    if (e.pre && !t.pre) return -1;
    if (t.pre && !e.pre) return 1;
  }
  return n;
};
function Up(e) {
  Li = false, Zl = true, e = e || /* @__PURE__ */ new Map(), vn.sort(h_);
  const t = (n) => Gp(e, n);
  try {
    for (sa = 0; sa < vn.length; sa++) {
      const n = vn[sa];
      if (n && n.active !== false) {
        if (t(n)) continue;
        Ts(n, null, 14);
      }
    }
  } finally {
    sa = 0, vn.length = 0, m_(e), Zl = false, (vn.length || or.length) && Up(e);
  }
}
function Gp(e, t) {
  if (!e.has(t)) e.set(t, 1);
  else {
    const n = e.get(t);
    if (n > d_) {
      const a = t.ownerInstance, r = a && Qp(a.type);
      return Zp(`Maximum recursive updates exceeded${r ? ` in component <${r}>` : ""}. This means you have a reactive effect that is mutating its own dependencies and thus recursively triggering itself. Possible sources include component template, render function, updated hook or watcher source function.`, null, 10), true;
    } else e.set(t, n + 1);
  }
}
const Or = /* @__PURE__ */ new Set();
t_().__VUE_HMR_RUNTIME__ = { createRecord: ai(g_), rerender: ai(w_), reload: ai(y_) };
const Wl = /* @__PURE__ */ new Map();
function g_(e, t) {
  return Wl.has(e) ? false : (Wl.set(e, { initialDef: Hr(t), instances: /* @__PURE__ */ new Set() }), true);
}
function Hr(e) {
  return x_(e) ? e.__vccOpts : e;
}
function w_(e, t) {
  const n = Wl.get(e);
  n && (n.initialDef.render = t, [...n.instances].forEach((a) => {
    t && (a.render = t, Hr(a.type).render = t), a.renderCache = [], a.effect.dirty = true, a.update();
  }));
}
function y_(e, t) {
  const n = Wl.get(e);
  if (!n) return;
  t = Hr(t), lc(n.initialDef, t);
  const a = [...n.instances];
  for (const r of a) {
    const l = Hr(r.type);
    Or.has(l) || (l !== n.initialDef && lc(l, t), Or.add(l)), r.appContext.propsCache.delete(r.type), r.appContext.emitsCache.delete(r.type), r.appContext.optionsCache.delete(r.type), r.ceReload ? (Or.add(l), r.ceReload(t.styles), Or.delete(l)) : r.parent ? (r.parent.effect.dirty = true, f_(r.parent.update)) : r.appContext.reload ? r.appContext.reload() : typeof window < "u" ? window.location.reload() : console.warn("[HMR] Root or manually mounted instance modified. Full reload required.");
  }
  v_(() => {
    for (const r of a) Or.delete(Hr(r.type));
  });
}
function lc(e, t) {
  Fp(e, t);
  for (const n in e) n !== "__file" && !(n in t) && delete e[n];
}
function ai(e) {
  return (t, n) => {
    try {
      return e(t, n);
    } catch (a) {
      console.error(a), console.warn("[HMR] Something went wrong during Vue component hot-reload. Full reload required.");
    }
  };
}
const b_ = /(?:^|[-_])(\w)/g, __ = (e) => e.replace(b_, (t) => t.toUpperCase()).replace(/[-_]/g, "");
function Qp(e, t = true) {
  return ko(e) ? e.displayName || e.name : e.name || t && e.__name;
}
function Xp(e, t, n = false) {
  let a = Qp(t);
  if (!a && t.__file) {
    const r = t.__file.match(/([^/\\]+)\.\w+$/);
    r && (a = r[1]);
  }
  if (!a && e && e.parent) {
    const r = (l) => {
      for (const o in l) if (l[o] === t) return o;
    };
    a = r(e.components || e.parent.type.components) || r(e.appContext.components);
  }
  return a ? __(a) : n ? "App" : "Anonymous";
}
function x_(e) {
  return ko(e) && "__vccOpts" in e;
}
function k_() {
  if (typeof window > "u") return;
  const e = { style: "color:#3ba776" }, t = { style: "color:#1677ff" }, n = { style: "color:#f5222d" }, a = { style: "color:#eb2f96" }, r = { header(u) {
    return ti(u) ? u.__isVue ? ["div", e, "VueInstance"] : zp(u) ? ["div", {}, ["span", e, d(u)], "<", i(u.value), ">"] : qp(u) ? ["div", {}, ["span", e, ni(u) ? "ShallowReactive" : "Reactive"], "<", i(u), `>${Di(u) ? " (readonly)" : ""}`] : Di(u) ? ["div", {}, ["span", e, ni(u) ? "ShallowReadonly" : "Readonly"], "<", i(u), ">"] : null : null;
  }, hasBody(u) {
    return u && u.__isVue;
  }, body(u) {
    if (u && u.__isVue) return ["div", {}, ...l(u.$)];
  } };
  function l(u) {
    const p = [];
    u.type.props && u.props && p.push(o("props", lr(u.props))), u.setupState !== ac && p.push(o("setup", u.setupState)), u.data !== ac && p.push(o("data", lr(u.data)));
    const v = s(u, "computed");
    v && p.push(o("computed", v));
    const b = s(u, "inject");
    return b && p.push(o("injected", b)), p.push(["div", {}, ["span", { style: a.style + ";opacity:0.66" }, "$ (internal): "], ["object", { object: u }]]), p;
  }
  function o(u, p) {
    return p = Fp({}, p), Object.keys(p).length ? ["div", { style: "line-height:1.25em;margin-bottom:0.6em" }, ["div", { style: "color:#476582" }, u], ["div", { style: "padding-left:1.25em" }, ...Object.keys(p).map((v) => ["div", {}, ["span", a, v + ": "], i(p[v], false)])]] : ["span", {}];
  }
  function i(u, p = true) {
    return typeof u == "number" ? ["span", t, u] : typeof u == "string" ? ["span", n, JSON.stringify(u)] : typeof u == "boolean" ? ["span", a, u] : ti(u) ? ["object", { object: p ? lr(u) : u }] : ["span", n, String(u)];
  }
  function s(u, p) {
    const v = u.type;
    if (ko(v)) return;
    const b = {};
    for (const h in u.ctx) c(v, h, p) && (b[h] = u.ctx[h]);
    return b;
  }
  function c(u, p, v) {
    const b = u[v];
    if (Yp(b) && b.includes(p) || ti(b) && p in b || u.extends && c(u.extends, p, v) || u.mixins && u.mixins.some((h) => c(h, p, v))) return true;
  }
  function d(u) {
    return ni(u) ? "ShallowRef" : u.effect ? "ComputedRef" : "Ref";
  }
  window.devtoolsFormatters ? window.devtoolsFormatters.push(r) : window.devtoolsFormatters = [r];
}
function S_() {
  console.info(`You are running a development build of Vue.
Make sure to use the production build (*.prod.js) when deploying for production.`), k_();
}
function C_(e) {
  Object.getOwnPropertySymbols(e).forEach((t) => {
  });
}
new RegExp("\\b" + "arguments,await,break,case,catch,class,const,continue,debugger,default,delete,do,else,export,extends,finally,for,function,if,import,let,new,return,super,switch,throw,try,var,void,while,with,yield".split(",").join("\\b|\\b") + "\\b");
const M_ = Symbol("vModelRadio"), T_ = Symbol("vModelCheckbox"), A_ = Symbol("vModelText"), D_ = Symbol("vModelSelect"), L_ = Symbol("vModelDynamic"), O_ = Symbol("vOnModifiersGuard"), P_ = Symbol("vOnKeysGuard"), $_ = Symbol("vShow"), R_ = Symbol("Transition"), E_ = Symbol("TransitionGroup");
C_({ [M_]: "vModelRadio", [T_]: "vModelCheckbox", [A_]: "vModelText", [D_]: "vModelSelect", [L_]: "vModelDynamic", [O_]: "withModifiers", [P_]: "withKeys", [$_]: "vShow", [R_]: "Transition", [E_]: "TransitionGroup" });
S_();
const N_ = { class: "wpuf-text-lg wpuf-font-bold wpuf-mb-0" }, I_ = { class: "wpuf-flex wpuf-flex-row-reverse wpuf-mt-8 wpuf-text-end" }, V_ = { __name: "Edit", emits: ["go-to-list", "checkIsDirty"], setup(e, { emit: t }) {
  const n = Yt(), a = pl();
  Ha();
  const r = t, l = () => {
    if (n.resetErrors(), !n.validateFields()) {
      n.isUpdating = false;
      return;
    }
    n.isUpdating = true, n.updateSubscription().then((o) => {
      o.success ? (a.display = true, a.type = "success", a.message = o.message, n.setSubscriptionsByStatus(n.currentSubscriptionStatus), n.getSubscriptionCount(), r("go-to-list")) : (a.display = true, a.type = "danger", a.message = o.message), setTimeout(() => {
        a.display = false, a.type = "", a.message = "";
      }, 3e3);
    }).finally(() => {
      n.isUpdating = false;
    });
  };
  return (o, i) => (T(), F("div", { class: pe([f(n).isUnsavedPopupOpen ? "wpuf-blur" : "", "wpuf-px-12"]) }, [L("h3", N_, ge(f(ke)("Edit Subscription", "wp-user-frontend")), 1), Ne(um), Ne(Bp), L("div", I_, [Ne(Ms, { onUpdateSubscription: l }), L("button", { onClick: i[0] || (i[0] = (s) => o.$emit("checkIsDirty", f(n).currentSubscriptionStatus)), type: "button", class: "wpuf-mr-[10px] wpuf-rounded-md wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50" }, ge(f(ke)("Cancel", "wp-user-frontend")), 1)])], 2));
} }, As = sl("quickEdit", { state: () => ({ isQuickEdit: te(false) }), actions: { setQuickEditStatus(e) {
  this.isQuickEdit = e;
} } }), j_ = { class: "wpuf-fixed wpuf-z-10", "aria-labelledby": "modal-title", role: "dialog", "aria-modal": "true" }, B_ = { class: "wpuf-fixed wpuf-inset-0 wpuf-z-10 wpuf-w-screen wpuf-overflow-y-auto" }, F_ = { class: "wpuf-flex wpuf-min-h-full wpuf-justify-center wpuf-text-center wpuf-items-center wpuf-p-0" }, Y_ = { class: "wpuf-relative wpuf-transform wpuf-overflow-hidden wpuf-rounded-lg wpuf-bg-white wpuf-px-4 wpuf-pb-4 wpuf-pt-5 wpuf-text-left wpuf-shadow-xl wpuf-transition-all wpuf-my-8 wpuf-w-full wpuf-max-w-lg wpuf-p-6" }, q_ = { class: "wpuf-absolute wpuf-right-0 wpuf-top-0 wpuf-pr-4 wpuf-pt-4 wpuf-block" }, z_ = { class: "wpuf-flex wpuf-items-start" }, H_ = { class: "wpuf-ml-4 wpuf-mt-0 wpuf-text-left" }, K_ = { class: "wpuf-text-base wpuf-font-semibold wpuf-leading-6 wpuf-text-gray-900", id: "modal-title" }, Z_ = { class: "wpuf-mt-2" }, W_ = { class: "wpuf-text-sm wpuf-text-gray-500" }, U_ = { class: "wpuf-mt-4 wpuf-flex wpuf-flex-row-reverse" }, G_ = { __name: "Popup", emits: ["deleteSubscription", "trashSubscription", "hidePopup"], setup(e, { emit: t }) {
  const n = t, a = Yt().currentSubscriptionStatus, r = J(() => {
    switch (a) {
      case "trash":
        return { title: ke("Delete Subscription", "wp-user-frontend"), message: ke("Are you sure you want to delete this subscription? This action cannot be undone.", "wp-user-frontend"), actionText: ke("Delete", "wp-user-frontend") };
      default:
        return { title: ke("Trash Subscription", "wp-user-frontend"), message: ke("This subscription will be moved to the trash. Are you sure?", "wp-user-frontend"), actionText: ke("Trash", "wp-user-frontend") };
    }
  }), l = () => {
    n(a === "trash" ? "deleteSubscription" : "trashSubscription");
  };
  return (o, i) => (T(), F("div", j_, [i[3] || (i[3] = L("div", { class: "wpuf-fixed wpuf-inset-0 wpuf-bg-gray-500 wpuf-bg-opacity-75 wpuf-transition-opacity" }, null, -1)), L("div", B_, [L("div", F_, [L("div", Y_, [L("div", q_, [L("button", { onClick: i[0] || (i[0] = (s) => o.$emit("hidePopup")), type: "button", class: "wpuf-rounded-md wpuf-bg-white wpuf-text-gray-400 hover:wpuf-text-gray-500 focus:wpuf-outline-none" }, i[2] || (i[2] = [L("span", { class: "wpuf-sr-only" }, "Close", -1), L("svg", { class: "wpuf-h-6 wpuf-w-6", fill: "none", viewBox: "0 0 24 24", "stroke-width": "1.5", stroke: "currentColor", "aria-hidden": "true" }, [L("path", { "stroke-linecap": "round", "stroke-linejoin": "round", d: "M6 18L18 6M6 6l12 12" })], -1)]))]), L("div", z_, [L("div", H_, [L("h3", K_, ge(r.value.title), 1), L("div", Z_, [L("p", W_, ge(r.value.message), 1)])])]), L("div", U_, [L("button", { type: "button", onClick: l, class: "wpuf-inline-flex wpuf-justify-center wpuf-rounded-md wpuf-bg-red-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-red-500 wpuf-ml-3 wpuf-w-auto" }, ge(r.value.actionText), 1), L("button", { type: "button", onClick: i[1] || (i[1] = (s) => o.$emit("hidePopup")), class: "wpuf-inline-flex wpuf-justify-center wpuf-rounded-md wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50 wpuf-mt-0 wpuf-w-auto" }, ge(f(ke)("Cancel", "wp-user-frontend")), 1)])])])])]));
} }, Q_ = { key: 0, class: "wpuf-text-base wpuf-justify-between wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-xl wpuf-shadow wpuf-relative" }, X_ = ["title"], J_ = { key: 0 }, e3 = { class: "wpuf-text-gray-500 wpuf-text-base wpuf-m-0" }, t3 = { class: "wpuf-absolute wpuf-right-6 wpuf-p-[10px] wpuf-rounded-full hover:wpuf-bg-gray-100 wpuf-top-4 wpuf-right-4" }, n3 = { key: 0, class: "wpuf-w-max wpuf--left-20 wpuf-absolute wpuf-rounded-xl wpuf-bg-white wpuf-shadow-lg wpuf-ring-1 wpuf-ring-gray-900/5 wpuf-overflow-hidden wpuf-z-10" }, a3 = { key: 0 }, r3 = { key: 1 }, l3 = { class: "wpuf-flex wpuf-px-6 wpuf-py-6 wpuf-justify-between wpuf-items-center" }, o3 = { key: 0, width: "24", height: "24", viewBox: "0 0 24 24", fill: "none", xmlns: "http://www.w3.org/2000/svg" }, i3 = { class: "wpuf-flex wpuf-px-6 wpuf-pb-6 wpuf-justify-between wpuf-items-center" }, s3 = { class: "wpuf-text-gray-500 wpuf-text-sm wpuf-m-0" }, u3 = { __name: "SubscriptionBox", props: { subscription: Object }, emits: ["toggleSubscriptionStatus"], setup(e, { emit: t }) {
  const n = e, { subscription: a } = Pt(n), r = te(false), l = te(true), o = te(""), i = te(false), s = te(0), c = te(0), d = wpufSubscriptions.siteUrl + "/wp-admin/edit.php?post_type=wpuf_subscription&page=wpuf_subscribers&post_ID=" + a.value.ID, u = Ha(), p = As(), v = Yt(), b = pl(), h = () => {
    const j = a.value.post_status;
    j === "publish" ? o.value = "wpuf-text-green-700 wpuf-bg-green-50 ring-green-600/20" : j === "private" ? o.value = "wpuf-text-orange-700 wpuf-bg-orange-50 wpuf-ring-orange-600/10" : j === "draft" ? o.value = "wpuf-text-yellow-700 wpuf-bg-yellow-50 wpuf-ring-yellow-600/10" : j === "pending" ? o.value = "wpuf-text-slate-700 wpuf-bg-slate-50 wpuf-ring-slate-600/10" : j === "trash" ? o.value = "wpuf-text-red-700 wpuf-bg-red-50 wpuf-ring-red-600/10" : o.value = "wpuf-text-green-700 wpuf-bg-green-50 ring-green-600/20";
  }, N = () => {
    i.value = true;
  }, I = () => {
    i.value = false;
  }, x = { beforeMount: (j) => {
    j.clickOutsideEvent = (y) => {
      j.contains(y.target) || (i.value = false);
    }, document.body.addEventListener("click", j.clickOutsideEvent);
  }, unmounted: (j) => {
    document.body.removeEventListener("click", j.clickOutsideEvent);
  } }, _ = () => {
    const j = { subscription_id: a.value.ID };
    Bt({ path: On("/wp-json/wpuf/v1/wpuf_subscription/subscribers", j), method: "GET", headers: { "X-WP-Nonce": wpufSubscriptions.nonce } }).then((y) => {
      c.value = y.subscribers, a.value.subscribers = c.value;
    }).catch((y) => {
      console.log(y);
    });
  }, g = () => {
    if (R.value) {
      const j = a.value.meta_value.cycle_period === "" ? ke("day", "wp-user-frontend") : a.value.meta_value.cycle_period, y = parseInt(a.value.meta_value._billing_cycle_number) === 0 || parseInt(a.value.meta_value._billing_cycle_number) === 1 ? "" : " " + a.value.meta_value._billing_cycle_number + " ";
      s.value = wpufSubscriptions.currencySymbol + a.value.meta_value.billing_amount + " per " + y + " " + j + "(s)";
    } else parseInt(a.value.meta_value.billing_amount) === 0 || a.value.meta_value.billing_amount === "" ? s.value = ke("Free", "wp-user-frontend") : s.value = wpufSubscriptions.currencySymbol + a.value.meta_value.billing_amount;
  }, R = J(() => a.value.meta_value.recurring_pay === "on" || a.value.meta_value.recurring_pay === "yes");
  yr(() => {
    h(), g(), _();
  });
  const M = J(() => a.value.post_title), C = (j) => {
    j.edit_row_name = "post_status", j.edit_row_value = "trash";
    const y = v.changeSubscriptionStatus(j);
    H(y);
  }, Y = (j) => {
    j.edit_row_name = "post_status", j.edit_row_value = "draft";
    const y = v.changeSubscriptionStatus(j);
    H(y);
  }, P = (j) => {
    const y = v.deleteSubscription(j.ID);
    H(y);
  }, $ = (j) => {
    j.edit_row_name = "post_status", j.edit_row_value = j.post_status === "draft" ? "publish" : "draft", v.isSubscriptionLoading = true;
    const y = v.changeSubscriptionStatus(j);
    H(y);
  }, H = (j) => {
    j.then((y) => {
      y.success ? (b.display = true, b.type = "success", b.message = y.message, r.value = false, l.value = false, v.isDirty = false, v.isUnsavedPopupOpen = false, v.setCurrentSubscription(null), v.setSubscriptionsByStatus(v.currentSubscriptionStatus), v.getSubscriptionCount()) : (b.display = true, b.type = "danger", b.message = y.message), setTimeout(() => {
        b.display = false, b.type = "", b.message = "";
      }, 3e3);
    });
  }, z = J(() => {
    let j = a.value.post_status;
    if (a.value.post_status === "publish") return j = "Published", j;
    const y = j.charAt(0).toUpperCase(), V = j.slice(1);
    return y + V;
  }), se = J(() => a.value.post_password !== "");
  return (j, y) => (T(), F(Ce, null, [l.value ? (T(), F("div", Q_, [L("div", { onClick: y[0] || (y[0] = (V) => f(a).post_status !== "trash" ? [f(u).setCurrentComponent("Edit"), f(v).setCurrentSubscription(f(a))] : ""), class: pe([f(a).post_status !== "trash" ? "wpuf-cursor-pointer" : "", "wpuf-flex wpuf-justify-between wpuf-border-b border-gray-900/5 wpuf-bg-gray-50 wpuf-p-6 wpuf-rounded-t-xl"]) }, [L("div", null, [L("div", { class: "wpuf-flex wpuf-py-1 wpuf-text-gray-900 wpuf-m-0 wpuf-font-medium", title: "id: " + f(a).ID }, [Ge(ge(M.value) + "   ", 1), se.value ? (T(), F("span", J_, y[10] || (y[10] = [L("svg", { width: "24", height: "24", viewBox: "0 0 24 24", fill: "none", xmlns: "http://www.w3.org/2000/svg" }, [L("path", { "fill-rule": "evenodd", "clip-rule": "evenodd", d: "M5.99999 10.8V8.4C5.99999 5.08629 8.68628 2.4 12 2.4C15.3137 2.4 18 5.08629 18 8.4V10.8C19.3255 10.8 20.4 11.8745 20.4 13.2V19.2C20.4 20.5255 19.3255 21.6 18 21.6H5.99999C4.67451 21.6 3.59999 20.5255 3.59999 19.2V13.2C3.59999 11.8745 4.67451 10.8 5.99999 10.8ZM15.6 8.4V10.8H8.39999V8.4C8.39999 6.41178 10.0118 4.8 12 4.8C13.9882 4.8 15.6 6.41178 15.6 8.4Z", fill: "#a0aec0" })], -1)]))) : Z("", true)], 8, X_), L("p", e3, ge(f(v).getReadableBillingAmount(f(a))), 1)])], 2), L("div", t3, [zn((T(), F("svg", { onClick: N, class: "wpuf-h-5 wpuf-w-5 hover:wpuf-cursor-pointer", viewBox: "0 0 20 20", fill: "currentColor", "aria-hidden": "true" }, y[11] || (y[11] = [L("path", { d: "M3 10a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM8.5 10a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM15.5 8.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3z" }, null, -1)]))), [[x, I]]), i.value ? (T(), F("div", n3, [f(a).post_status !== "trash" ? (T(), F("ul", a3, [L("li", { onClick: y[1] || (y[1] = (V) => {
    f(u).setCurrentComponent("Edit"), f(v).setCurrentSubscription(f(a));
  }), class: "wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer" }, ge(f(ke)("Edit", "wp-user-frontend")), 1), L("li", { onClick: y[2] || (y[2] = (V) => {
    f(p).isQuickEdit = true, f(v).setCurrentSubscription(f(a)), f(v).currentSubscriptionCopy = f(a);
  }), class: "wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer" }, ge(f(ke)("Quick Edit", "wp-user-frontend")), 1), L("li", { onClick: y[3] || (y[3] = (V) => $(f(a))), class: "wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer" }, ge(f(a).post_status === "publish" ? f(ke)("Draft", "wp-user-frontend") : f(ke)("Publish", "wp-user-frontend")), 1), L("li", { onClick: y[4] || (y[4] = (V) => r.value = true), class: "wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer" }, ge(f(ke)("Trash", "wp-user-frontend")), 1)])) : Z("", true), f(a).post_status === "trash" ? (T(), F("ul", r3, [L("li", { onClick: y[5] || (y[5] = (V) => {
    Y(f(a)), f(u).setCurrentComponent("List");
  }), class: "wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer" }, ge(f(ke)("Restore", "wp-user-frontend")), 1), L("li", { onClick: y[6] || (y[6] = (V) => r.value = true), class: "wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer" }, ge(f(ke)("Delete Permanently", "wp-user-frontend")), 1)])) : Z("", true)])) : Z("", true)]), L("div", l3, [L("div", { class: pe([o.value, "wpuf-text-sm wpuf-w-fit wpuf-px-2.5 wpuf-py-1 wpuf-shadow-sm wpuf-bg-gray-100 wpuf-rounded-md wpuf-ring-1"]) }, ge(z.value), 3), R.value ? (T(), F("svg", o3, y[12] || (y[12] = [L("path", { d: "M20 19C20 19.5523 20.4477 20 21 20C21.5523 20 22 19.5523 22 19L20 19ZM21 15.375L22 15.375L22 14.375H21V15.375ZM12 21L12 22L12 21ZM4.06195 13.0013C3.99361 12.4532 3.49394 12.0644 2.9459 12.1327C2.39786 12.201 2.00898 12.7007 2.07732 13.2488L4.06195 13.0013ZM20.3458 15.375L20.3458 14.375L20.3458 14.375L20.3458 15.375ZM17.375 14.375C16.8227 14.375 16.375 14.8227 16.375 15.375C16.375 15.9273 16.8227 16.375 17.375 16.375L17.375 14.375ZM4.00001 5.00002C4.00001 4.44773 3.55229 4.00002 3.00001 4.00002C2.44772 4.00002 2.00001 4.44773 2.00001 5.00002L4.00001 5.00002ZM3.00001 8.62502L2.00001 8.62502L2.00001 9.62502H3.00001V8.62502ZM3.65421 8.62502L3.65421 9.62502L3.65421 9.62502L3.65421 8.62502ZM12 3.00002L12 2.00002L12 3.00002ZM6.62501 9.62502C7.17729 9.62502 7.62501 9.1773 7.62501 8.62502C7.62501 8.07273 7.17729 7.62502 6.62501 7.62502L6.62501 9.62502ZM19.9381 10.9988C20.0064 11.5468 20.5061 11.9357 21.0541 11.8673C21.6022 11.799 21.991 11.2993 21.9227 10.7513L19.9381 10.9988ZM12.8552 9.58595C13.1788 10.0335 13.804 10.134 14.2515 9.81034C14.699 9.48673 14.7995 8.86159 14.4759 8.41404L12.8552 9.58595ZM12.5 7C12.5 6.44771 12.0523 6 11.5 6C10.9477 6 10.5 6.44771 10.5 7H12.5ZM10.5 17C10.5 17.5523 10.9477 18 11.5 18C12.0523 18 12.5 17.5523 12.5 17L10.5 17ZM10.1448 14.414C9.82121 13.9665 9.19606 13.866 8.74852 14.1896C8.30098 14.5133 8.20051 15.1384 8.52412 15.5859L10.1448 14.414ZM22 19L22 15.375L20 15.375L20 19L22 19ZM12 20C7.92115 20 4.55392 16.9466 4.06195 13.0013L2.07732 13.2488C2.69257 18.1827 6.89973 22 12 22L12 20ZM19.4189 14.9998C18.2313 17.9335 15.3558 20 12 20L12 22C16.1983 22 19.79 19.4132 21.2727 15.7502L19.4189 14.9998ZM21 14.375H20.3458V16.375H21V14.375ZM20.3458 14.375L17.375 14.375L17.375 16.375L20.3458 16.375L20.3458 14.375ZM2.00001 5.00002L2.00001 8.62502L4.00001 8.62502L4.00001 5.00002L2.00001 5.00002ZM4.58115 9.00023C5.76867 6.06656 8.6442 4.00002 12 4.00002L12 2.00002C7.80171 2.00002 4.21 4.58686 2.72728 8.2498L4.58115 9.00023ZM3.00001 9.62502H3.65421V7.62502H3.00001V9.62502ZM3.65421 9.62502L6.62501 9.62502L6.62501 7.62502L3.65421 7.62502L3.65421 9.62502ZM12 4.00002C16.0789 4.00001 19.4461 7.05347 19.9381 10.9988L21.9227 10.7513C21.3074 5.81736 17.1003 2.00001 12 2.00002L12 4.00002ZM11.5 11C10.4518 11 10 10.3556 10 10H8C8 11.8535 9.78676 13 11.5 13V11ZM10 10C10 9.64441 10.4518 9 11.5 9V7C9.78676 7 8 8.14644 8 10H10ZM11.5 9C12.1534 9 12.6379 9.28548 12.8552 9.58595L14.4759 8.41404C13.8286 7.51891 12.6973 7 11.5 7V9ZM11.5 13C12.5482 13 13 13.6444 13 14H15C15 12.1464 13.2132 11 11.5 11V13ZM10.5 7V8H12.5V7H10.5ZM10.5 16L10.5 17L12.5 17L12.5 16L10.5 16ZM11.5 15C10.8466 15 10.3621 14.7145 10.1448 14.414L8.52412 15.5859C9.17138 16.4811 10.3027 17 11.5 17L11.5 15ZM13 14C13 14.3556 12.5482 15 11.5 15V17C13.2132 17 15 15.8535 15 14H13Z", fill: "rgb(107 114 128)" }, null, -1)]))) : Z("", true)]), L("div", i3, [L("p", s3, ge(f(ke)("Total Subscribers")), 1), L("a", { href: d, class: "wpuf-text-gray-500" }, ge(c.value), 1)])])) : Z("", true), r.value ? (T(), Pe(G_, { key: 1, onHidePopup: y[7] || (y[7] = (V) => r.value = false), onTrashSubscription: y[8] || (y[8] = (V) => C(f(a))), onDeleteSubscription: y[9] || (y[9] = (V) => P(f(a))) })) : Z("", true)], 64));
} }, c3 = { class: "wpuf-h-[50vh] wpuf-flex wpuf-items-center wpuf-justify-center" }, d3 = { class: "wpuf-w-3/4 wpuf-text-center" }, p3 = { key: 0, class: "wpuf-mx-auto wpuf-h-12 wpuf-w-12 wpuf-text-gray-400", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", "aria-hidden": "true" }, f3 = { key: 1, class: "wpuf-text-3xl wpuf-text-gray-900" }, v3 = { class: "wpuf-text-sm wpuf-text-gray-500 wpuf-text-center wpuf-mt-8" }, m3 = { key: 2, class: "wpuf-mt-12" }, Jp = { __name: "Empty", props: { message: { type: String, default: ke("No Subscription created yet!", "wp-user-frontend") } }, setup(e) {
  const t = Ha(), n = Yt(), a = e;
  return (r, l) => (T(), F("div", c3, [L("div", d3, [f(n).currentSubscriptionStatus === "all" ? (T(), F("svg", p3, l[1] || (l[1] = [L("path", { "vector-effect": "non-scaling-stroke", "stroke-linecap": "round", "stroke-linejoin": "round", "stroke-width": "2", d: "M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" }, null, -1)]))) : Z("", true), f(n).currentSubscriptionStatus === "all" ? (T(), F("h3", f3, ge(f(ke)("No Subscription created yet!", "wp-user-frontend")), 1)) : Z("", true), L("p", v3, ge(a.message), 1), f(n).currentSubscriptionStatus === "all" ? (T(), F("div", m3, [L("button", { type: "button", onClick: l[0] || (l[0] = (o) => f(t).setCurrentComponent("New")), class: "wpuf-rounded-md wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:wpuf-outline focus-visible:wpuf-outline-2 focus-visible:wpuf-outline-offset-2 focus-visible:wpuf-outline-indigo-600" }, [l[2] || (l[2] = L("span", { class: "dashicons dashicons-plus-alt" }, null, -1)), Ge("    " + ge(f(ke)("Add Subscription", "wp-user-frontend")), 1)])])) : Z("", true)])]));
} }, h3 = { class: "wpuf-flex wpuf-items-center wpuf-justify-between wpuf-border-t wpuf-border-gray-200 wpuf-bg-white wpuf-py-3 wpuf-px-6 wpuf-mt-16" }, g3 = { class: "wpuf-flex wpuf-flex-1 wpuf-items-center wpuf-justify-between" }, w3 = { class: "wpuf-text-sm wpuf-text-gray-700" }, y3 = { class: "wpuf-font-medium" }, b3 = { class: "wpuf-font-medium" }, _3 = { class: "wpuf-font-medium" }, x3 = { key: 0 }, k3 = { class: "isolate wpuf-inline-flex wpuf--space-x-px wpuf-rounded-md wpuf-shadow-sm", "aria-label": "Pagination" }, S3 = ["disabled"], C3 = ["onClick"], M3 = ["disabled"], T3 = { __name: "Pagination", props: { currentPage: { type: Number, required: true }, count: { type: Number, required: true }, maxVisibleButtons: { type: Number, required: true }, totalPages: { type: Number, required: true }, perPage: { type: Number, required: true } }, emits: ["changePageTo"], setup(e, { emit: t }) {
  const n = Yt(), a = e, r = t, l = te(a.currentPage), o = te(a.count), i = te(a.maxVisibleButtons), s = te(a.totalPages), c = parseInt(a.perPage), d = J(() => l.value === 1), u = J(() => l.value === s.value), p = J(() => l.value === 1 || s.value <= i.value ? 1 : l.value === s.value ? s.value - i.value : l.value - 1), v = J(() => (l.value - 1) * c + 1), b = J(() => Math.min(l.value * c, o.value)), h = J(() => {
    const x = [];
    for (let _ = p.value; _ <= Math.min(p.value + i.value - 1, s.value); _++) x.push({ name: _, isDisabled: _ === l });
    return x;
  }), N = () => {
    l.value = 1, r("changePageTo", 1);
  }, I = () => {
    l.value = s.value, r("changePageTo", s.value);
  };
  return He(() => n.currentSubscriptionStatus, (x) => {
    o.value = n.allCount[x], s.value = Math.ceil(o.value / wpufSubscriptions.perPage);
  }), (x, _) => (T(), F("div", h3, [L("div", g3, [L("div", null, [L("p", w3, [_[0] || (_[0] = Ge(" Showing ")), L("span", y3, ge(v.value), 1), _[1] || (_[1] = Ge(" to ")), L("span", b3, ge(b.value), 1), _[2] || (_[2] = Ge(" of ")), L("span", _3, ge(o.value), 1), _[3] || (_[3] = Ge(" results "))])]), o.value > f(c) ? (T(), F("div", x3, [L("nav", k3, [L("button", { onClick: N, disabled: d.value, class: pe([d.value ? "wpuf-bg-gray-50 wpuf-cursor-not-allowed" : "", "wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-rounded-l-md wpuf-px-2 wpuf-py-2 wpuf-text-gray-400 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50 focus:wpuf-z-20 focus:outline-offset-0"]) }, _[4] || (_[4] = [L("span", { class: "wpuf-sr-only" }, "Previous", -1), L("svg", { class: "wpuf-h-5 wpuf-w-5", viewBox: "0 0 20 20", fill: "currentColor", "aria-hidden": "true" }, [L("path", { "fill-rule": "evenodd", d: "M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z", "clip-rule": "evenodd" })], -1)]), 10, S3), (T(true), F(Ce, null, Ve(h.value, (g) => (T(), F("button", { onClick: (R) => [r("changePageTo", g.name)], key: g.name, class: pe([l.value === g.name ? "wpuf-bg-indigo-600 wpuf-text-white hover:wpuf-bg-indigo-700" : "", "wpuf-relative wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50 focus:wpuf-z-20 focus:outline-offset-0 wpuf-inline-flex"]) }, ge(g.name), 11, C3))), 128)), L("button", { onClick: I, disabled: u.value, class: pe([u.value ? "wpuf-bg-gray-50 wpuf-cursor-not-allowed" : "", "wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-rounded-r-md wpuf-px-2 wpuf-py-2 wpuf-text-gray-400 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50 focus:wpuf-z-20 focus:outline-offset-0"]) }, _[5] || (_[5] = [L("span", { class: "wpuf-sr-only" }, "Next", -1), L("svg", { class: "wpuf-h-5 wpuf-w-5", viewBox: "0 0 20 20", fill: "currentColor", "aria-hidden": "true" }, [L("path", { "fill-rule": "evenodd", d: "M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z", "clip-rule": "evenodd" })], -1)]), 10, M3)])])) : Z("", true)])]));
} }, A3 = { class: "wpuf-text-lg wpuf-font-bold wpuf-m-0" }, D3 = { class: "wpuf-text-sm wpuf-text-gray-500 wpuf-mb-0" }, oc = { __name: "ListHeader", props: { message: { type: String, default: ke("Explore and manage all subscriptions in one place", "wp-user-frontend") } }, setup(e) {
  const t = Ha(), n = Yt();
  Ya(t);
  const a = e, r = J(() => {
    switch (n.currentSubscriptionStatus) {
      case "all":
        return ke("All Subscriptions", "wp-user-frontend");
      case "publish":
        return ke("Published", "wp-user-frontend");
      case "draft":
        return ke("Drafts", "wp-user-frontend");
      case "trash":
        return ke("Trash", "wp-user-frontend");
      default:
        return ke("Subscriptions", "wp-user-frontend");
    }
  });
  return (l, o) => (T(), F(Ce, null, [L("h3", A3, ge(r.value), 1), L("p", D3, ge(a.message), 1)], 64));
} }, L3 = { key: 0, class: "wpuf-flex wpuf-h-svh wpuf-items-center wpuf-justify-center" }, O3 = { key: 1 }, P3 = { key: 0, class: "wpuf-pl-[48px]" }, $3 = { key: 1, class: "wpuf-pl-[48px]" }, R3 = { class: "wpuf-grid wpuf-grid-cols-3 wpuf-gap-4 wpuf-mt-[40px]" }, E3 = { __name: "List", setup(e) {
  const t = Yt(), n = Ya(t).subscriptionList, a = te(t.allCount.all), r = Ya(t).currentPageNumber, l = parseInt(wpufSubscriptions.perPage), o = te(Math.ceil(a.value / wpufSubscriptions.perPage)), i = te(3), s = te(0), c = (p) => {
    const v = (p - 1) * parseInt(wpufSubscriptions.perPage);
    t.setSubscriptionsByStatus(t.currentSubscriptionStatus, v), r.value = p, s.value += 1;
  }, d = { all: ke("Powerful Subscription Features for Monetizing Your Content. Unlock a World of Possibilities with WPUF's Subscription Features – From Charging Users for Posting to Exclusive Content Access.", "wp-user-frontend"), publish: ke("Ops! It looks like you haven't published any subscriptions yet. To create a new subscription and start monetizing your content, click the 'Add Subscription' button above.", "wp-user-frontend"), draft: ke("Ops! It looks like you haven't saved any subscriptions as drafts yet.", "wp-user-frontend"), trash: ke("Your trash is empty! If you delete a subscription, it will be moved here.", "wp-user-frontend") }, u = { all: ke("Manage and monitor all your subscriptions. Edit details or create new ones as needed.", "wp-user-frontend"), publish: ke("Oversee all active subscriptions currently available for users.", "wp-user-frontend"), draft: ke("Handle subscriptions that are saved as drafts but not yet published.", "wp-user-frontend"), trash: ke("Review deleted subscriptions. Restore or permanently delete them as required.", "wp-user-frontend") };
  return yr(() => {
    a.value = t.allCount[t.currentSubscriptionStatus], o.value = Math.ceil(a.value / wpufSubscriptions.perPage);
  }), He(() => t.currentSubscriptionStatus, (p) => {
    a.value = t.allCount[p], o.value = Math.ceil(a.value / wpufSubscriptions.perPage), r.value = 1;
  }), He(() => t.allCount, (p) => {
    a.value = t.allCount[t.currentSubscriptionStatus], o.value = Math.ceil(a.value / wpufSubscriptions.perPage), s.value += 1;
  }), (p, v) => (T(), F(Ce, null, [f(t).isSubscriptionLoading ? (T(), F("div", L3, [Ne(f(Od), { "animation-duration": 1e3, "dot-size": 20, "dots-num": 3, color: "#7DC442" })])) : Z("", true), f(t).isSubscriptionLoading ? Z("", true) : (T(), F("div", O3, [a.value ? (T(), F("div", $3, [Ne(oc, { message: u[f(t).currentSubscriptionStatus] }, null, 8, ["message"]), L("div", R3, [(T(true), F(Ce, null, Ve(f(n), (b) => (T(), Pe(u3, { subscription: b, key: b.ID }, null, 8, ["subscription"]))), 128))])])) : (T(), F("div", P3, [Ne(oc, { message: u[f(t).currentSubscriptionStatus] }, null, 8, ["message"]), Ne(Jp, { message: d[f(t).currentSubscriptionStatus] }, null, 8, ["message"])])), a.value > f(l) ? (T(), Pe(T3, { key: s.value, currentPage: f(r), count: a.value, maxVisibleButtons: i.value, totalPages: o.value, perPage: f(l), onChangePageTo: c }, null, 8, ["currentPage", "count", "maxVisibleButtons", "totalPages", "perPage"])) : Z("", true)]))], 64));
} }, N3 = { class: "wpuf-text-lg wpuf-font-bold wpuf-mb-0" }, I3 = { class: "wpuf-flex wpuf-flex-row-reverse wpuf-mt-8 wpuf-text-end" }, V3 = { __name: "New", setup(e) {
  const t = Ha(), n = Yt(), a = pl();
  yr(() => {
    n.setBlankSubscription();
  });
  const r = () => {
    if (n.isUpdating = true, n.resetErrors(), !n.validateFields()) {
      n.isUpdating = false;
      return;
    }
    n.isSubscriptionLoading = true, n.updateSubscription().then((l) => {
      l.success ? (a.display = true, a.type = "success", a.message = l.message, n.setSubscriptionsByStatus(n.currentSubscriptionStatus), t.setCurrentComponent("List"), n.getSubscriptionCount()) : (a.display = true, a.type = "danger", a.message = l.message), n.isUpdating = false, setTimeout(() => {
        a.display = false, a.type = "", a.message = "";
      }, 3e3);
    }).finally(() => {
      n.isSubscriptionLoading = false;
    });
  };
  return (l, o) => (T(), F("div", { class: pe([f(n).isUnsavedPopupOpen ? "wpuf-blur" : "", "wpuf-px-12"]) }, [L("h3", N3, ge(f(ke)("New Subscription", "wp-user-frontend")), 1), Ne(Bp), L("div", I3, [Ne(Ms, { buttonText: "Save", onUpdateSubscription: r }), L("button", { onClick: o[0] || (o[0] = (i) => l.$emit("checkIsDirty", f(n).currentSubscriptionStatus)), type: "button", class: "wpuf-mr-[10px] wpuf-rounded-md wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50" }, ge(f(ke)("Cancel", "wp-user-frontend")), 1)])], 2));
} };
function j3(e, t) {
  return T(), F("svg", { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 20 20", fill: "currentColor", "aria-hidden": "true", "data-slot": "icon" }, [L("path", { "fill-rule": "evenodd", d: "M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z", "clip-rule": "evenodd" })]);
}
const B3 = { class: "wpuf-rounded-lg wpuf-fixed wpuf-z-20 wpuf-top-1/3 wpuf-left-[calc(50%-5rem)] wpuf-w-1/3 wpuf-bg-white wpuf-p-6 wpuf-border wpuf-border-gray-200 wpuf-shadow" }, F3 = { class: "wpuf-px-2" }, Y3 = { for: "plan-name", class: "wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900" }, q3 = { class: "wpuf-relative wpuf-mt-2 wpuf-rounded-md wpuf-shadow-sm" }, z3 = ["value"], H3 = { key: 0, class: "wpuf-pointer-events-none wpuf-absolute wpuf-inset-y-0 wpuf-right-0 wpuf-flex wpuf-items-center wpuf-pr-3" }, K3 = { key: 0, class: "wpuf-mt-2 wpuf-text-sm wpuf-text-red-600", id: "email-error" }, Z3 = { class: "wpuf-px-2 wpuf-mt-4" }, W3 = { for: "date", class: "wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900" }, U3 = { key: 0, class: "wpuf-mt-2 wpuf-text-sm wpuf-text-red-600", id: "email-error" }, G3 = { class: "wpuf-px-2 wpuf-mt-4" }, Q3 = { key: 0, id: "filled_error_help", class: "wpuf-mt-2 wpuf-text-xs wpuf-text-red-600" }, X3 = { class: "wpuf-flex wpuf-mt-8 wpuf-flex-row-reverse" }, J3 = ["disabled"], e5 = { __name: "QuickEdit", setup(e) {
  const t = Yt(), n = pl(), a = t.currentSubscription, r = te(a.post_title), l = te(new Date(a.post_date)), { errors: o } = Ya(Fe(t));
  t.fieldNames;
  const i = As(), s = (u) => {
    const p = u.getFullYear(), v = u.getMonth() + 1 < 10 ? "0" + (u.getMonth() + 1) : u.getMonth() + 1, b = u.getDate() < 10 ? "0" + u.getDate() : u.getDate(), h = u.getHours() < 10 ? "0" + u.getHours() : u.getHours(), N = u.getMinutes() < 10 ? "0" + u.getMinutes() : u.getMinutes(), I = u.getSeconds() < 10 ? "0" + u.getSeconds() : u.getSeconds();
    return p + "-" + v + "-" + b + " " + h + ":" + N + ":" + I;
  }, c = (u) => {
    a.post_date = s(u);
  }, d = () => {
    if (t.isUpdating = true, t.resetErrors(), a.post_title = r.value, !t.validateFields("quickEdit")) {
      t.isUpdating = false;
      return;
    }
    t.updateSubscription().then((u) => {
      u.success ? (n.display = true, n.type = "success", n.message = u.message, setTimeout(() => {
        n.display = false, n.type = "", n.message = "";
      }, 3e3), i.isQuickEdit = false) : (t.updateError.status = true, t.updateError.message = u.message);
    }), t.isUpdating = false;
  };
  return (u, p) => (T(), F("div", B3, [L("div", F3, [L("label", Y3, ge(f(ke)("Plan name", "wp-user-frontend")), 1), L("div", q3, [L("input", { type: "text", name: "plan-name", id: "plan-name", class: pe([f(o).planName ? "!wpuf-border-red-500 wpuf-ring-red-300 placeholder:wpuf-text-red-300 !wpuf-text-red-900 focus:wpuf-ring-red-500" : "", "wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm"]), "aria-invalid": "true", "aria-describedby": "plan-name-error", onChange: p[0] || (p[0] = (v) => r.value = v.target.value), value: r.value }, null, 42, z3), f(o).planName ? (T(), F("div", H3, [Ne(f(j3), { class: "wpuf-h-5 wpuf-w-5 wpuf-text-red-500", "aria-hidden": "true" })])) : Z("", true)]), f(o).planName ? (T(), F("p", K3, ge(f(o).planName.message), 1)) : Z("", true)]), L("div", Z3, [L("label", W3, ge(f(ke)("Date", "wp-user-frontend")), 1), L("div", { class: pe([f(o).date ? "wpuf-border wpuf-border-red-500 placeholder:wpuf-text-red-300 !wpuf-text-red-900 focus:wpuf-ring-red-500" : "wpuf-ring-indigo-600", "wpuf-relative wpuf-mt-2 wpuf-rounded-md wpuf-shadow-sm"]) }, [Ne(f(wo), { textInput: "", modelValue: l.value, "onUpdate:modelValue": [p[1] || (p[1] = (v) => l.value = v), c], state: !f(o).date, "is-24": false, "enable-seconds": "" }, null, 8, ["modelValue", "state"])], 2), f(o).date ? (T(), F("p", U3, ge(f(ke)("Not a valid date", "wp-user-frontend")), 1)) : Z("", true)]), L("div", G3, [f(t).updateError.status ? (T(), F("p", Q3, ge(f(t).updateError.message), 1)) : Z("", true)]), L("div", X3, [Ne(Ms, { onUpdateSubscription: d }), L("button", { onClick: p[2] || (p[2] = (v) => [f(i).setQuickEditStatus(false), f(t).errors = {}]), disabled: f(t).isUpdating, type: "button", class: pe([f(t).isUpdating ? "wpuf-cursor-not-allowed wpuf-bg-gray-50" : "", "wpuf-rounded-lg wpuf-mr-4 wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50"]) }, ge(f(ke)("Cancel", "wp-user-frontend")), 11, J3)])]));
} }, t5 = Cs(e5, [["__scopeId", "data-v-44675312"]]), n5 = { class: "wpuf-flex wpuf-items-center wpuf-justify-between" }, a5 = { key: 0, class: "wpuf-w-5 wpuf-h-5", "aria-hidden": "true", xmlns: "http://www.w3.org/2000/svg", fill: "currentColor", viewBox: "0 0 20 20" }, r5 = { key: 1, class: "wpuf-w-5 wpuf-h-5", "aria-hidden": "true", xmlns: "http://www.w3.org/2000/svg", fill: "currentColor", viewBox: "0 0 20 20" }, l5 = { class: "ms-3 wpuf-text-sm wpuf-font-normal" }, o5 = { __name: "Notice", props: { type: { type: String, required: true }, message: { type: String, required: true }, index: { type: Number, required: true } }, setup(e) {
  const t = e, n = t.type, a = t.message, r = "toast-" + n, l = { success: "wpuf-text-green-500 wpuf-bg-green-100", danger: "wpuf-text-red-500 wpuf-bg-red-100" };
  return (o, i) => (T(), F("div", { id: r, class: "wpuf-flex wpuf-justify-between wpuf-items-center wpuf-w-full wpuf-max-w-xs wpuf-p-4 wpuf-mb-4 wpuf-text-gray-500 wpuf-bg-white wpuf-rounded-lg wpuf-shadow", role: "alert" }, [L("div", n5, [L("div", { class: pe([l[f(n)], "wpuf-mr-2 wpuf-rounded-lg wpuf-flex wpuf-items-center wpuf-justify-center wpuf-w-8 wpuf-h-8"]) }, [f(n) === "success" ? (T(), F("svg", a5, i[1] || (i[1] = [L("path", { d: "M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" }, null, -1)]))) : Z("", true), f(n) === "danger" ? (T(), F("svg", r5, i[2] || (i[2] = [L("path", { d: "M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V6a1 1 0 0 1 2 0v5Z" }, null, -1)]))) : Z("", true)], 2), L("div", l5, ge(f(a)), 1)]), L("button", { onClick: i[0] || (i[0] = (s) => o.$emit("removeNotice", e.index)), type: "button", class: "ms-auto wpuf--mx-1.5 wpuf--my-1.5 wpuf-bg-white wpuf-text-gray-400 hover:wpuf-text-gray-900 wpuf-rounded-lg focus:wpuf-ring-2 focus:wpuf-ring-gray-300 wpuf-p-1.5 hover:wpuf-bg-gray-100 wpuf-inline-flex wpuf-items-center wpuf-justify-center wpuf-h-8 wpuf-w-8", "data-dismiss-target": "#toast-success", "aria-label": "Close" }, i[3] || (i[3] = [L("span", { class: "wpuf-sr-only" }, "Close", -1), L("svg", { class: "wpuf-w-3 wpuf-h-3", "aria-hidden": "true", xmlns: "http://www.w3.org/2000/svg", fill: "none", viewBox: "0 0 14 14" }, [L("path", { stroke: "currentColor", "stroke-linecap": "round", "stroke-linejoin": "round", "stroke-width": "2", d: "m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" })], -1)]))]));
} }, i5 = { class: "wpuf-h-80" }, s5 = { class: "wpuf-fixed wpuf-inset-0 wpuf-z-50 wpuf-flex wpuf-items-center wpuf-justify-center wpuf-p-4 sm:wpuf-p-0" }, u5 = { class: "wpuf-mx-auto wpuf-w-2/5 wpuf-overflow-hidden wpuf-rounded-lg wpuf-bg-white wpuf-shadow-xl" }, c5 = { class: "wpuf-relative wpuf-p-5" }, d5 = { class: "wpuf-text-center" }, p5 = { class: "wpuf-mt-2 wpuf-text-sm text-secondary-500" }, f5 = { class: "wpuf-text-base" }, v5 = { class: "wpuf-mt-5 wpuf-flex wpuf-justify-end wpuf-gap-3" }, m5 = { __name: "Unsaved", setup(e) {
  return (t, n) => (T(), F("div", i5, [n[5] || (n[5] = L("div", { class: "wpuf-fixed wpuf-inset-0 wpuf-z-10 bg-secondary-700/50" }, null, -1)), L("div", s5, [L("div", u5, [L("div", c5, [L("div", d5, [n[4] || (n[4] = L("div", { class: "wpuf-mx-auto wpuf-mb-5 wpuf-flex wpuf-h-10 wpuf-w-10 wpuf-items-center wpuf-justify-center wpuf-rounded-full wpuf-bg-yellow-100 wpuf-text-yellow-500" }, [L("svg", { xmlns: "http://www.w3.org/2000/svg", fill: "none", viewBox: "0 0 24 24", "stroke-width": "1.5", stroke: "currentColor", class: "wpuf-h-6 wpuf-w-6" }, [L("path", { "stroke-linecap": "round", "stroke-linejoin": "round", d: "M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 019 9v.375M10.125 2.25A3.375 3.375 0 0113.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 013.375 3.375M9 15l2.25 2.25L15 12" })])], -1)), L("div", null, [n[3] || (n[3] = L("h3", { class: "wpuf-text-lg wpuf-font-medium text-secondary-900" }, "Unsaved changes", -1)), L("div", p5, [L("p", f5, [Ge(ge(f(ke)("You have unsaved changes in your current subscription.", "wp-user-frontend")), 1), n[2] || (n[2] = L("br", null, null, -1)), Ge(ge(f(ke)("Navigating away from this page will cause your work to be lost.", "wp-user-frontend")), 1)])])])]), L("div", v5, [L("button", { onClick: n[0] || (n[0] = (a) => t.$emit("goToList")), type: "button", class: "wpuf-rounded-lg wpuf-border wpuf-border-red-500 wpuf-bg-red-500 wpuf-px-4 wpuf-py-2 wpuf-text-center wpuf-text-sm wpuf-font-medium wpuf-text-white wpuf-shadow-sm wpuf-transition-all hover:wpuf-border-red-700 hover:wpuf-bg-red-700 focus:wpuf-ring focus:wpuf-ring-red-200" }, ge(f(ke)("Discard Changes", "wp-user-frontend")), 1), L("button", { onClick: n[1] || (n[1] = (a) => t.$emit("closePopup")), type: "button", class: "wpuf-rounded-lg wpuf-border wpuf-border-blue-500 wpuf-bg-blue-500 wpuf-px-4 wpuf-py-2 wpuf-text-center wpuf-text-sm wpuf-font-medium wpuf-text-white wpuf-shadow-sm wpuf-transition-all hover:wpuf-border-blue-700 hover:wpuf-bg-blue-700 focus:wpuf-ring focus:wpuf-ring-blue-200" }, ge(f(ke)("Continue", "wp-user-frontend")), 1)])])])])]));
} }, h5 = { class: "wpuf-flex wpuf-items-center wpuf-justify-between wpuf-mt-[32px] wpuf-leading-none wpuf-px-[20px]" }, g5 = { class: "wpuf-text-[24px] wpuf-font-semibold wpuf-my-0" }, w5 = { class: "wpuf-flex wpuf-justify-end wpuf-h-max" }, y5 = { __name: "ContentHeader", setup(e) {
  const t = Ha(), n = Yt(), a = J(() => !(n.currentSubscriptionStatus === "trash" || n.currentSubscriptionStatus === "all" && n.allCount.all === 0));
  return (r, l) => (T(), F("div", h5, [L("h3", g5, ge(f(ke)("Subscriptions", "wp-user-frontend")), 1), L("div", w5, [a.value ? (T(), F("button", { key: 0, onClick: l[0] || (l[0] = (o) => f(t).setCurrentComponent("New")), type: "button", class: "wpuf-flex wpuf-items-center wpuf-rounded-md wpuf-bg-indigo-600 hover:wpuf-bg-indigo-500 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600" }, [l[1] || (l[1] = L("span", { class: "wpuf-mr-2" }, [L("svg", { width: "24", height: "24", viewBox: "0 0 24 24", fill: "none", xmlns: "http://www.w3.org/2000/svg" }, [L("path", { "fill-rule": "evenodd", "clip-rule": "evenodd", d: "M12 21.6C17.3019 21.6 21.6 17.3019 21.6 12C21.6 6.69807 17.3019 2.4 12 2.4C6.69806 2.4 2.39999 6.69807 2.39999 12C2.39999 17.3019 6.69806 21.6 12 21.6ZM13.2 8.4C13.2 7.73726 12.6627 7.2 12 7.2C11.3372 7.2 10.8 7.73726 10.8 8.4V10.8H8.39999C7.73725 10.8 7.19999 11.3373 7.19999 12C7.19999 12.6627 7.73725 13.2 8.39999 13.2H10.8V15.6C10.8 16.2627 11.3372 16.8 12 16.8C12.6627 16.8 13.2 16.2627 13.2 15.6V13.2H15.6C16.2627 13.2 16.8 12.6627 16.8 12C16.8 11.3373 16.2627 10.8 15.6 10.8H13.2V8.4Z", fill: "#FFF" })])], -1)), Ge(" " + ge(f(ke)("Add Subscription", "wp-user-frontend")), 1)])) : Z("", true)])]));
} }, b5 = { key: 0, class: "wpuf-flex wpuf-h-svh wpuf-items-center wpuf-justify-center" }, _5 = { class: "wpuf-basis-1/5 wpuf-border-r-2 wpuf-border-gray-200" }, x5 = { class: "wpuf-basis-4/5" }, k5 = { class: "wpuf-fixed wpuf-top-20 wpuf-right-8 wpuf-z-10" }, S5 = { __name: "Subscriptions", setup(e) {
  const t = Ha(), n = Yt(), a = As(), r = pl();
  Ya(t);
  const { notices: l } = Ya(r), o = te(null), i = te("all"), s = te(0), c = te(0);
  lo("wpufSubscriptions", wpufSubscriptions), yr(() => {
    n.setSubscriptionsByStatus(n.currentSubscriptionStatus).then((v) => {
      n.subscriptionList ? t.setCurrentComponent("List") : t.setCurrentComponent("Empty"), s.value += 1;
    }), n.getSubscriptionCount();
  });
  const d = (v = "all") => {
    n.isDirty ? (n.isUnsavedPopupOpen = true, i.value = v) : (n.isDirty = false, n.isUnsavedPopupOpen = false, n.setSubscriptionsByStatus(v), t.setCurrentComponent("List"), n.setCurrentSubscription(null), n.getSubscriptionCount(), n.currentPageNumber = 1);
  }, u = () => {
    n.isDirty = false, n.isUnsavedPopupOpen = false, n.setSubscriptionsByStatus(i.value), t.setCurrentComponent("List"), n.setCurrentSubscription(null), n.currentPageNumber = 1;
  }, p = (v) => {
    r.removeNotice(v), c.value += 1;
  };
  return He(() => t.currentComponent, (v) => {
    switch (v) {
      case "List":
        o.value = E3;
        break;
      case "Edit":
        o.value = V_;
        break;
      case "New":
        o.value = V3;
        break;
      default:
        o.value = Jp;
    }
    n.resetErrors();
  }), (v, b) => (T(), F(Ce, null, [Ne(_1), f(n).isSubscriptionLoading || o.value === null ? (T(), F("div", b5, [Ne(f(Od), { "animation-duration": 1e3, "dot-size": 20, "dots-num": 3, color: "#7DC442" })])) : Z("", true), f(a).isQuickEdit ? (T(), F("div", { key: 1, onClick: b[0] || (b[0] = (h) => [f(a).setQuickEditStatus(false), f(n).errors = {}]), class: "wpuf-absolute wpuf-w-full wpuf-h-screen wpuf-z-10 wpuf-left-[-20px]" })) : Z("", true), f(a).isQuickEdit ? (T(), Pe(t5, { key: 2 })) : Z("", true), Ne(y5), f(n).isSubscriptionLoading ? Z("", true) : (T(), F("div", { key: 3, class: pe([f(a).isQuickEdit ? "wpuf-blur" : "", "wpuf-flex wpuf-pt-[40px] wpuf-pr-[20px] wpuf-pl-[20px]"]) }, [L("div", _5, [(T(), Pe(v0, null, [Ne(W1, { onCheckIsDirty: d })], 1024))]), L("div", x5, [(T(), Pe(ol(o.value), { key: s.value, onGoToList: u, onCheckIsDirty: d }, null, 32))]), f(n).isUnsavedPopupOpen ? (T(), Pe(m5, { key: 0, onClosePopup: b[1] || (b[1] = (h) => f(n).isUnsavedPopupOpen = false), onGoToList: u })) : Z("", true)], 2)), L("div", k5, [f(r).display ? (T(true), F(Ce, { key: 0 }, Ve(f(l), (h, N) => (T(), Pe(o5, { key: c.value, index: N, type: h.type, message: h.message, onRemoveNotice: (I) => p(N) }, null, 8, ["index", "type", "message", "onRemoveNotice"]))), 128)) : Z("", true)])], 64));
} };
window.wpufSubscriptions = wpufSubscriptions;
const C5 = Hv(), ef = Fv(S5);
ef.use(C5);
ef.mount("#wpuf-subscription-page");
//# sourceMappingURL=subscriptions.js.map
