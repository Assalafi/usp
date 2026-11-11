import React, {useEffect, useLayoutEffect, useRef, useState} from 'react';

export const schools = [
    {key:'ug',label:'Undergraduate',short:'UG',color:'#635bff'},
    {key:'pg',label:'Postgraduate',short:'PG',color:'#18a999'},
    {key:'sbrs',label:'SBRS',short:'SBRS',color:'#ea9b35'},
    {key:'institute',label:'Institute',short:'INST',color:'#3b8fd9'},
];
export const money = v => new Intl.NumberFormat('en-NG',{style:'currency',currency:'NGN',maximumFractionDigits:2}).format(Number(v)||0);
export const number = v => new Intl.NumberFormat('en-NG').format(Number(v)||0);
export const time = v => v && !isNaN(Date.parse(v)) ? new Date(v).toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit',second:'2-digit'}) : '—';
export const date = v => v && !isNaN(Date.parse(v)) ? new Date(v).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : 'Not recorded';
export function FitMoney({value,className}) {
    const ref=useRef();
    useLayoutEffect(()=>{
        const el=ref.current;let width=-1;
        function fit(){
            const available=el.parentElement.clientWidth;
            if(available===width)return; width=available;
            el.style.fontSize='';
            const size=parseFloat(getComputedStyle(el).fontSize);
            if(el.scrollWidth>el.clientWidth)el.style.fontSize=(Math.floor(size*el.clientWidth/el.scrollWidth*10)/10)+'px';
        }
        fit();const observer=new ResizeObserver(fit);observer.observe(el.parentElement);
        return()=>observer.disconnect();
    },[value]);
    return <strong ref={ref} className={className} style={{whiteSpace:'nowrap',display:'block',minWidth:0}}>{money(value)}</strong>;
}
export async function request(url, options={}) {
    const r = await fetch(url,{credentials:'same-origin',cache:'no-store',...options,headers:{Accept:'application/json',...options.headers}});
    let d; try {d=await r.json();} catch(_) {throw new Error('This section could not be loaded. Please check your sign-in and try again.');}
    if(!r.ok || d.success===false) throw new Error(Object.values(d.errors||{}).flat().join(' ')||d.message||'The request could not be completed. Please try again.');
    return d;
}
export function useFeed(url, interval=10000, paused=false) {
    const [state,setState]=useState({url:null,data:null,error:'',busy:false});
    const [revision,setRevision]=useState(0);
    useEffect(()=>{
        let disposed=false,timer,controller,inFlight=false;
        if(!url){setState({url:null,data:null,error:'',busy:false});return;}
        async function load(){
            if(disposed||inFlight||document.hidden)return;
            inFlight=true;const started=Date.now();controller=new AbortController();
            setState(s=>({url,data:s.url===url?s.data:null,error:'',busy:true}));
            let timedOut=false;const timeout=setTimeout(()=>{timedOut=true;controller.abort();},30000);
            try {const data=await request(url,{signal:controller.signal});if(!disposed)setState({url,data,error:'',busy:false});}
            catch(e){if(!disposed)setState(s=>({...s,url,busy:false,error:timedOut?'This update is taking longer than expected. We will try again shortly.':e.message}));}
            finally{clearTimeout(timeout);inFlight=false;if(!disposed&&interval&&!paused)timer=setTimeout(load,Math.max(1000,interval-(Date.now()-started)));}
        }
        function visibility(){clearTimeout(timer);if(!document.hidden&&!paused)load();}
        load();document.addEventListener('visibilitychange',visibility);
        return()=>{disposed=true;clearTimeout(timer);controller?.abort();document.removeEventListener('visibilitychange',visibility);};
    },[url,revision,interval,paused]);
    return {...(state.url===url?state:{data:null,error:'',busy:!!url}),refresh:()=>setRevision(v=>v+1)};
}
export function Icon({name,size=18,...props}) {
    const paths={
        arrow:<><path d="M5 12h14M13 6l6 6-6 6"/></>,
        refresh:<><path d="M20 7v5h-5M4 17v-5h5"/><path d="M6 7a7 7 0 0112-1l2 6M4 12l2 6a7 7 0 0012-1"/></>,
        filter:<><path d="M4 6h16M7 12h10M10 18h4"/><circle cx="8" cy="6" r="2"/><circle cx="15" cy="12" r="2"/></>,
        search:<><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></>,
        expand:<path d="M9 3H3v6m12-6h6v6M3 15v6h6m12-6v6h-6"/>,
        wallet:<><path d="M4 7V5a2 2 0 012-2h13v4M3 7h18v14H3z"/><path d="M21 12h-6v4h6"/></>,
        check:<><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></>,
        clock:<><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></>,
        chevron:<path d="m8 10 4 4 4-4"/>,
        close:<path d="m6 6 12 12M6 18 18 6"/>,
        download:<><path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/></>,
        building:<><path d="m3 8 9-5 9 5M4 9h16M6 10v8m6-8v8m6-8v8M3 21h18"/></>,
    };
    return <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.65" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>{paths[name]||paths.wallet}</svg>;
}
export function Button({icon,children,primary=false,className='',...props}) {return <button type="button" className={'fr-btn '+(primary?'fr-primary ':'')+className} {...props}>{icon&&<Icon name={icon}/>} {children}</button>;}
export function Loading({width='70%'}) {return <span className="fr-skeleton" style={{width}} aria-label="Loading"/>;}
export function Alert({children,onRetry}) {return children?<div className="fr-alert" role="status"><Icon name="clock"/><span>{children}</span>{onRetry&&<Button onClick={onRetry}>Retry</Button>}</div>:null;}
export function LiveTag({busy,error,enabled=true}) {return <span className={'fr-live '+(error?'fr-delayed':'')}><i className={busy?'fr-pulse':''}/>{error?'Update delayed':!enabled?'Ready':busy?'Updating':'Live'}</span>;}
export function Header({mode,session,onSession,config,feed,children}) {
    return <header className="fr-header"><div><div className="fr-eyebrow">UNIVERSITY OF MAIDUGURI <span>/</span> FINANCE</div><h1>{mode==='live'?'Collections overview':'Fees & payments'} <LiveTag {...feed}/></h1></div><div className="fr-header-actions"><label className="fr-session"><span>Academic session</span><select aria-label="Academic session" value={session} onChange={e=>onSession(e.target.value)}>{config.sessions.map(s=><option key={s}>{s}</option>)}</select><Icon name="chevron" size={14}/></label>{children}</div></header>;
}
export function normalize(data, live=false) {
    const list=live?(data?.systems||[]).map(s=>({key:s.source,status:s.status,amount:s.paid_amount,payments:s.paid_count,services:(s.services||[]).map(r=>({label:r.label,amount:r.gross_amount,payments:r.invoice_count||r.payment_count||0}))})):(data?.schools||[]);
    return schools.map(meta=>({...meta,...list.find(s=>s.key===meta.key),status:list.find(s=>s.key===meta.key)?.status||'loading'}));
}
export function totals(list){const good=list.filter(s=>s.status==='ok');return {amount:good.reduce((a,s)=>a+Number(s.amount||0),0),payments:good.reduce((a,s)=>a+Number(s.payments||0),0),reporting:good.length};}
export function Donut({list,active,onSelect}) {
    const total=totals(list).amount;let offset=0;const length=2*Math.PI*74;
    return <div className="fr-donut"><svg viewBox="0 0 200 200" role="img" aria-label="Share of confirmed collections by school"><circle cx="100" cy="100" r="74" fill="none" stroke="#edf0f5" strokeWidth="19"/>{list.filter(s=>s.status==='ok'&&s.amount>0).map(s=>{const portion=s.amount/total*length,start=offset;offset+=portion;return <circle key={s.key} cx="100" cy="100" r="74" fill="none" stroke={s.color} strokeWidth={active===s.key?23:19} strokeDasharray={`${Math.max(0,portion-3)} ${length}`} strokeDashoffset={-start} transform="rotate(-90 100 100)" opacity={active&&active!==s.key?.toString()?0.35:1}><title>{s.label+': '+money(s.amount)}</title></circle>;})}</svg><div><strong>{active?(100*(list.find(s=>s.key===active)?.amount||0)/(total||1)).toFixed(1)+'%':totals(list).reporting+'/4'}</strong><span>{active?list.find(s=>s.key===active)?.label:'schools reporting'}</span></div></div>;
}
export function Mix({list,selected,onSelect}) {
    const total=totals(list).amount;
    return <section className="fr-mix fr-card"><div className="fr-card-title"><h2>Collection mix</h2><span>By school</span></div><div className="fr-mix-content"><Donut list={list} active={selected}/><div className="fr-legend">{list.map(s=><button key={s.key} type="button" onClick={()=>onSelect(selected===s.key?null:s.key)} aria-pressed={selected===s.key}><span className="fr-dot" style={{background:s.color}}/><span>{s.label}</span><strong>{s.status==='ok'?(100*(s.amount||0)/(total||1)).toFixed(1)+'%':'—'}</strong></button>)}</div></div></section>;
}
export function SchoolServices({school,selected,onSelect}) {
    const ready=school.status==='ok',services=(school.services||[]).filter(s=>!/refund/i.test(s.label));const max=Math.max(...services.map(s=>Number(s.amount)||0),1);
    return <article className={'fr-school-card '+(selected?'fr-highlight':'')} style={{'--school':school.color}} id={'fr-school-'+school.key}><header><div className="fr-school-badge">{school.short}</div><h3>{school.label}</h3><span className={'fr-source '+(!ready?'fr-offline':'')}>{ready?'Updated':school.status==='loading'?'Loading':'Unavailable'}</span></header><div className="fr-school-amount">{ready?money(school.amount):school.status==='loading'?<Loading/>:'—'}</div><div className="fr-school-sub"><span>{ready?number(school.payments)+' payments received':'Waiting for confirmed figures'}</span>{onSelect&&<button type="button" aria-label={'View '+school.label+' records'} onClick={onSelect}><Icon name="arrow" size={16}/></button>}</div><div className="fr-service-title"><span>Payment description</span><span>Collected</span></div><div className="fr-service-scroll">{ready?(services.length?services.map(s=><div className="fr-service" key={s.label}><div><span>{s.label}</span><strong>{money(s.amount)}</strong></div><div className="fr-service-bar"><span style={{width:Math.max(1,s.amount/max*100)+'%'}}/></div><small>{number(s.payments)} payments</small></div>):<div className="fr-empty-small">No confirmed payments for this session.</div>):<div className="fr-empty-small">{school.status==='loading'?'Loading payment descriptions…':'This school is temporarily unavailable. Its figures are excluded from the total.'}</div>}</div></article>;
}
export function Metric({label,value,note,icon,tone=''}) {return <div className={'fr-metric '+tone}><span className="fr-metric-label"><Icon name={icon}/>{label}</span><strong>{value??<Loading/>}</strong><small>{note}</small></div>;}
