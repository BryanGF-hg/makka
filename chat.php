<?php /* chat.php — Makka AI v6: visibility + historia con copiar + prompts editables + rename + drag paneles */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Makka AI — Multi-Agent Workspace</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
<style>
@import url(https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500;600;700;800&display=swap);

:root{
  color-scheme:light;
  --bg:#F4F7F5;--panel:#FFFFFF;--panel2:#F8FAF9;
  --text:#20332B;--muted:#84948B;--border:#E4EBE6;
  --green:#3E7C6B;--green-light:#E5F2EB;
  --orange:#FF8A3D;--yellow:#F2C14E;--red:#D64545;
  --shadow:0 5px 22px rgba(29,62,47,.055);
  --sidebar-w:310px;--right-sidebar-w:260px;
}
body.dark{
  color-scheme:dark;
  --bg:#111C18;--panel:#192720;--panel2:#1E3028;
  --text:#E4EFE8;--muted:#91A79A;--border:#30453A;
  --green:#91CCB0;--green-light:#294638;
  --shadow:0 5px 22px rgba(0,0,0,.14);
}
body.mint{color-scheme:light;--bg:#e8f7f1;--panel:#fff;--panel2:#F8FAF9;--text:#0f3b32;--muted:#3b6a5e;--border:#cbe8dd;--green:#2d7d99;--green-light:#E5F2EB;--shadow:0 5px 22px rgba(45,125,153,.08)}
body.lilac{color-scheme:dark;--bg:#1a1526;--panel:#241c36;--panel2:#2b223f;--text:#f0e9b0;--muted:#9a8db8;--border:#3a2e52;--green:#d4de4f;--green-light:rgba(212,222,79,.12);--shadow:0 5px 22px rgba(0,0,0,.4)}

*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-family:"DM Sans",sans-serif;height:100vh;overflow:hidden;transition:background .25s,color .25s}
button,input,select,textarea{font:inherit}
button{cursor:pointer}
a{color:var(--green)}

.topbar{height:66px;background:var(--panel);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 22px;position:relative;z-index:20}
.brand{display:flex;align-items:center;gap:11px}
.brand-icon{width:34px;height:34px;border-radius:11px;background:var(--green);color:var(--bg);display:grid;place-items:center;font-weight:800;font-size:18px}
.brand > div:last-child{font-weight:800;font-size:18px;letter-spacing:-.5px}
.brand small{display:block;font-size:9px;color:var(--muted);font-weight:600;letter-spacing:1.4px;margin-top:1px}
.top-actions{display:flex;align-items:center;gap:11px}
.status{font-size:11px;color:var(--muted);display:flex;align-items:center;gap:7px}
.dot{width:8px;height:8px;background:#35b879;border-radius:50%;display:inline-block}
.power{border:0;border-radius:20px;padding:8px 15px;color:#fff;background:#35a875;font-size:11px;font-weight:800;display:flex;align-items:center;gap:7px}
.power.off{background:#89968f}
.power:disabled{opacity:.6;cursor:wait}
.icon-button{border:1px solid var(--border);background:var(--panel);color:var(--text);width:36px;height:36px;border-radius:10px;font-size:16px}
#mobileMenu{display:none}

.settings-wrap{position:relative}
.dropdown{display:none;position:absolute;right:0;top:45px;width:320px;background:var(--panel);border:1px solid var(--border);border-radius:14px;padding:15px;box-shadow:var(--shadow);z-index:30;max-height:calc(100vh - 100px);overflow-y:auto}
.dropdown.open{display:block}
.dropdown h3{font-size:12px;margin:0 0 12px}
.setting-row{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid var(--border);font-size:11px;gap:8px}
.setting-row:last-child{border:0}
.setting-btn{border:1px solid var(--border);background:var(--panel);color:var(--text);padding:6px 10px;border-radius:6px;font-size:11px;font-weight:600;outline:none;cursor:pointer;white-space:nowrap}
.setting-btn:hover{border-color:var(--green);color:var(--green)}

.switch-btn{width:34px;height:19px;border-radius:20px;border:0;background:#b6c1ba;padding:2px;display:flex;align-items:center;cursor:pointer;transition:background .2s;flex-shrink:0}
.switch-btn span{height:15px;width:15px;border-radius:50%;background:#fff;transition:.2s;display:block}
.switch-btn.active{background:var(--green);justify-content:flex-end}

.layout{height:calc(100vh - 66px);display:grid;grid-template-columns:var(--sidebar-w) minmax(0,1fr) var(--right-sidebar-w);min-height:0;position:relative;transition:grid-template-columns .25s ease}
body.focus-mode .layout{grid-template-columns:0 minmax(0,1fr) 0}
body.focus-mode .sidebar, body.focus-mode .right-sidebar{opacity:0;pointer-events:none;overflow:hidden}
body.focus-mode .sidebar-toggle-float{display:none}
body.right-collapsed .layout{grid-template-columns:var(--sidebar-w) minmax(0,1fr) 0}
body.right-collapsed .right-sidebar{opacity:0;pointer-events:none;overflow:hidden}
@media (max-width:1200px){body.right-collapsed .layout{grid-template-columns:var(--sidebar-w) minmax(0,1fr)}}
.sidebar-toggle-float{position:absolute;top:50%;transform:translateY(-50%);left:calc(var(--sidebar-w) - 7px);width:14px;height:80px;z-index:10;border:1px solid var(--border);border-left:0;border-radius:0 6px 6px 0;background:var(--panel);color:var(--muted);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;box-shadow:2px 0 6px rgba(0,0,0,.03)}
.sidebar-toggle-float:hover{background:var(--green-light);color:var(--green)}

.sidebar{background:var(--panel);border-right:1px solid var(--border);overflow-y:auto;padding:18px 15px}
.right-sidebar{background:var(--panel);border-left:1px solid var(--border);overflow-y:auto;padding:18px 15px}
.side-heading{display:flex;justify-content:space-between;align-items:center;margin:3px 2px 10px;cursor:pointer;padding:6px 4px;border-radius:6px;user-select:none}
.side-heading:hover{background:var(--panel2)}
.side-heading strong{font-size:11px;text-transform:uppercase;letter-spacing:1px}
.side-heading span{font:10px"DM Mono";color:var(--muted)}
.side-heading .chevron{color:var(--muted);font-size:11px;transition:transform .2s ease;margin-left:6px}
.side-heading.collapsed .chevron{transform:rotate(-90deg)}
.section{margin-bottom:16px}
.section-body{overflow:hidden;transition:max-height .25s ease,opacity .2s ease}
.section-body.collapsed{max-height:0!important;opacity:0;margin:0}

.search-bar{position:relative;margin-bottom:8px}
.search-bar input{width:100%;padding:8px 10px 8px 32px;border:1px solid var(--border);border-radius:8px;background:var(--panel);font-size:11px;outline:0;color:var(--text)}
.search-bar input:focus{border-color:var(--green)}
.search-bar::before{content:'🔍';position:absolute;left:9px;top:50%;transform:translateY(-50%);font-size:11px;opacity:.5;pointer-events:none}

.conv-toolbar{display:flex;gap:4px;margin-bottom:8px}
.conv-toolbar button{flex:1;padding:6px 4px;font-size:10px;border:1px solid var(--border);background:var(--panel);border-radius:6px;color:var(--muted);font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.conv-toolbar button:hover{border-color:var(--green);color:var(--green)}
.conv-toolbar button.active{background:var(--green-light);border-color:var(--green);color:var(--green)}

.multi-actions{display:none;gap:4px;margin-bottom:8px;padding:6px;background:var(--green-light);border-radius:8px;font-size:10px;align-items:center;flex-wrap:wrap}
.multi-actions.visible{display:flex}
.multi-actions .count{font-weight:700;color:var(--green);padding:0 4px;flex-basis:100%;margin-bottom:2px;font-size:11px}
.multi-actions button{flex:1;min-width:52px;padding:5px 6px;border:1px solid var(--green);background:var(--panel);border-radius:5px;font-size:10px;font-weight:600;color:var(--green)}
.multi-actions button:hover{background:var(--green);color:var(--bg)}
.multi-actions button.cancel{flex:1 1 100%;background:transparent;border-color:var(--border);color:var(--muted)}

.session-tree{margin-top:4px}
.tree-group{margin-bottom:8px}
.tree-group-header{display:flex;align-items:center;gap:5px;padding:5px 6px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);cursor:pointer;border-radius:5px;user-select:none}
.tree-group-header:hover{background:var(--panel2)}
.tree-group-header .chev{font-size:9px;transition:transform .2s;flex-shrink:0}
.tree-group-header.collapsed .chev{transform:rotate(-90deg)}
.tree-group-header .icon{font-size:11px}
.tree-group-header .name{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.tree-group-header .badge{background:var(--panel2);padding:1px 6px;border-radius:8px;font-size:9px;color:var(--muted);font-weight:600}
.tree-group-header .group-btn{border:0;background:transparent;padding:2px 4px;border-radius:3px;font-size:10px;color:var(--muted);opacity:.4}
.tree-group-header .group-btn:hover{opacity:1;color:var(--green)}
.tree-group-header .group-btn.danger:hover{color:var(--red)}
.tree-group-body{padding-left:2px;padding-bottom:4px}
.tree-group-body.collapsed{display:none}
.tree-empty{font-size:10px;color:var(--muted);padding:4px 8px;font-style:italic;opacity:.7}

.session-item{display:flex;align-items:center;gap:5px;padding:6px 8px;border-radius:6px;font-size:11px;cursor:pointer;margin-bottom:1px;border:1px solid transparent;position:relative}
.session-item:hover{background:var(--panel2)}
.session-item.active{background:var(--green-light);border-color:var(--green);color:var(--green);font-weight:600}
.session-item .check{display:none;width:14px;height:14px;border:1.5px solid var(--border);border-radius:4px;align-items:center;justify-content:center;font-size:9px;background:var(--panel)}
.session-item.multi .check{display:flex}
.session-item.selected{background:var(--green-light);border-color:var(--green)}
.session-item.selected .check{background:var(--green);border-color:var(--green);color:var(--bg)}
.session-item .pin-btn{font-size:10px;opacity:.15;cursor:pointer}
.session-item:hover .pin-btn{opacity:.7}
.session-item.pinned .pin-btn{opacity:1}
.session-item .name{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0}
.session-item .date{font-size:9px;color:var(--muted);opacity:.7;white-space:nowrap;font-family:'DM Mono',monospace}
.session-item .match-count{font-size:9px;background:var(--green);color:var(--bg);padding:1px 5px;border-radius:8px;font-weight:700;flex-shrink:0}
.session-item .action-btn{border:0;background:transparent;padding:0 3px;color:var(--muted);opacity:0;cursor:pointer;font-size:11px}
.session-item:hover .action-btn{opacity:.5}
.session-item .action-btn:hover{opacity:1;color:var(--green)}
.session-item .action-btn.danger:hover{color:var(--red)}
.session-item mark{background:var(--yellow);color:var(--text);padding:0 2px;border-radius:2px}

.folder-menu{position:fixed;background:var(--panel);border:1px solid var(--border);border-radius:10px;box-shadow:var(--shadow);padding:4px;z-index:300;min-width:180px;max-height:280px;overflow-y:auto;animation:fadeIn .15s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:translateY(0)}}
.folder-menu-item{padding:7px 10px;border-radius:5px;cursor:pointer;font-size:11px;display:flex;align-items:center;gap:6px}
.folder-menu-item:hover{background:var(--panel2)}
.folder-menu-item.new{color:var(--green);font-weight:600;border-top:1px solid var(--border);margin-top:4px;padding-top:8px}

.bot-card{border:1px solid var(--border);background:var(--panel2);border-radius:12px;padding:12px;margin-bottom:8px}
.bot-card.working{border-color:var(--orange)}
.bot-card.offline{opacity:.55}
.bot-top{display:flex;align-items:center;gap:9px}
.bot-avatar{width:30px;height:30px;display:grid;place-items:center;border-radius:9px;background:var(--green-light);color:var(--green);font-size:12px;font-weight:800}
.bot-card.working .bot-avatar{animation:workPulse 1.4s infinite}
@keyframes workPulse{0%{box-shadow:0 0 0 0 rgba(255,138,61,.5)}70%{box-shadow:0 0 0 8px rgba(255,138,61,0)}100%{box-shadow:0 0 0 0 rgba(255,138,61,0)}}
.bot-name{font-size:12px;font-weight:800}
.bot-model{font-size:9px;color:var(--muted);margin-top:2px}
.bot-state{margin-left:auto;font-size:9px;color:#31a877}
.bot-card.working .bot-state{color:var(--orange);animation:stateBlink 1s infinite}
.bot-card.offline .bot-state{color:var(--muted);animation:none}
@keyframes stateBlink{0%,100%{opacity:1}50%{opacity:.4}}
.thought{border-top:1px solid var(--border);margin-top:10px;padding-top:9px;color:var(--muted);font-size:10px;line-height:1.6}
.thought strong{color:var(--green);font-weight:700}

.metric-head{display:flex;justify-content:space-between;align-items:center}
.metric-head strong{font-size:12px}
.metric-head span{font:11px"DM Mono";color:var(--green)}
.chart-wrap{background:var(--panel2);border:1px solid var(--border);border-radius:12px;padding:12px;margin-top:10px}
.chart-wrap svg{width:100%;height:72px;display:block;overflow:visible}
.chart-labels{display:flex;justify-content:space-between;font:9px"DM Mono";color:var(--muted)}
.token-breakdown{display:grid;gap:8px;margin-top:12px}
.token-row{display:flex;justify-content:space-between;font-size:10px;color:var(--muted)}
.token-row b{color:var(--text);font-family:'DM Mono',monospace}

.logs{background:var(--panel2);border:1px solid var(--border);border-radius:12px;padding:10px;max-height:180px;overflow:auto}
.log-line{font:9px"DM Mono";line-height:1.6;padding:5px 0;border-bottom:1px solid var(--border);color:var(--muted);word-break:break-all}
.log-line:last-child{border:0}
.log-time{color:var(--green);margin-right:6px}
.log-ok{color:#36a978}.log-warn{color:#d69c32}.log-err{color:var(--red)}
.log-section{color:var(--green);opacity:.55;text-align:center;font-size:8px;letter-spacing:1.5px;padding:4px 0;border:0}

.symbol-list{display:flex;flex-direction:column;gap:4px}
.symbol-item{font-family:"DM Mono",monospace;font-size:12px;padding:6px 8px;border-radius:6px;cursor:pointer;display:flex;align-items:center;gap:6px}
.symbol-item:hover{background:var(--panel2);color:var(--green)}
.symbol-item .icon{font-size:10px;opacity:.6}
.symbol-item .type{font-size:9px;background:var(--panel2);padding:2px 5px;border-radius:4px;color:var(--muted);margin-left:auto}

.workspace{min-width:0;min-height:0;display:flex;flex-direction:column;background:var(--bg)}
.chat-wrapper{display:flex;flex:1;min-height:0;overflow:hidden}
.chat-history{flex:1;overflow:auto;padding:27px clamp(18px,5vw,75px) 0;display:flex;flex-direction:column;position:relative}

/* MINIMAP con números + copiar */
.chat-minimap{width:60px;background:var(--panel);border-left:1px solid var(--border);padding:8px 4px;display:flex;flex-direction:column;gap:3px;overflow-y:auto;flex-shrink:0;scrollbar-width:none}
.chat-minimap::-webkit-scrollbar{display:none}
.mini-row{display:flex;gap:2px;align-items:center;transition:opacity .2s}
.mini-row.hidden-role{opacity:.25;pointer-events:none}
.mini-num{flex:1;height:22px;padding:0;border:1px solid var(--border);border-radius:5px;background:var(--panel2);color:var(--muted);font-size:10px;font-weight:700;font-family:'DM Mono',monospace;cursor:pointer;transition:all .15s}
.mini-num:hover{background:var(--green-light);border-color:var(--green);color:var(--green)}
.mini-row.active .mini-num{background:var(--green);border-color:var(--green);color:var(--bg)}
.mini-row.user .mini-num{border-left:3px solid var(--green)}
.mini-row.ai .mini-num{border-left:3px solid var(--muted)}
.mini-copy{width:22px;height:22px;padding:0;border:1px solid var(--border);border-radius:5px;background:var(--panel2);cursor:pointer;font-size:9px;opacity:.4;transition:opacity .15s, background .15s}
.mini-row:hover .mini-copy{opacity:1}
.mini-copy:hover{background:var(--green-light);border-color:var(--green)}

.chat-heading{display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);padding-bottom:16px;margin-bottom:20px;gap:12px;flex-wrap:wrap}
.chat-heading h1{font-size:15px;margin:0;font-weight:800;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:pointer}
.chat-heading h1:hover{color:var(--green)}
.chat-heading p{font-size:10px;margin:4px 0 0;color:var(--muted)}
.chat-heading-actions{display:flex;align-items:center;gap:6px;flex-shrink:0;flex-wrap:wrap;justify-content:flex-end}
.chat-heading .session{font:10px"DM Mono";color:var(--muted);margin-left:6px}
.icon-action{width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--panel);color:var(--muted);cursor:pointer;display:grid;place-items:center;font-size:13px;transition:all .15s}
.icon-action:hover{color:var(--green);border-color:var(--green)}
.icon-action.danger:hover{color:var(--red);border-color:var(--red)}
.icon-action.has-prompt{background:var(--green-light);color:var(--green);border-color:var(--green)}
.icon-action.active{background:var(--green-light);color:var(--green);border-color:var(--green);font-weight:700}

/* Ocultar por rol */
body.hide-user-msgs .message.user{display:none!important}
body.hide-ai-msgs .message.assistant{display:none!important}

.sys-prompt-banner{background:var(--green-light);border:1px solid var(--green);border-radius:10px;padding:8px 12px;margin-bottom:14px;font-size:11px;color:var(--green);display:flex;align-items:center;gap:8px}
.sys-prompt-banner b{font-weight:700}
.sys-prompt-banner .label{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sys-prompt-banner button{border:0;background:transparent;color:var(--green);cursor:pointer;font-size:11px;padding:2px 6px;border-radius:4px}
.sys-prompt-banner button:hover{background:rgba(62,124,107,.15)}

.empty-chat{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 20px;text-align:center;color:var(--muted)}
.empty-chat .mark{width:44px;height:44px;border-radius:14px;background:var(--green-light);color:var(--green);display:grid;place-items:center;font-size:20px;font-weight:800;margin-bottom:14px}
.empty-chat p{font-size:13px;margin:0 0 4px;font-weight:600;color:var(--text)}
.empty-chat small{font-size:11px;opacity:.8}

.message{display:flex;gap:6px;max-width:820px;margin:12px auto;align-items:flex-start;width:100%;position:relative}
.message.user{flex-direction:row-reverse}
.message-bubble{padding:12px 15px;border-radius:14px;background:var(--panel);border:1px solid var(--border);box-shadow:var(--shadow);font-size:13px;line-height:1.7;max-width:80%;word-wrap:break-word;min-width:0;position:relative}
.message-bubble p{margin:0 0 8px}
.message-bubble p:last-child{margin:0}
.message.user .message-bubble{background:var(--green-light);border-color:transparent}
.message-bubble h1,.message-bubble h2,.message-bubble h3{margin:14px 0 8px;line-height:1.3}
.message-bubble h1{font-size:20px}.message-bubble h2{font-size:17px}.message-bubble h3{font-size:15px}
.message-bubble ul,.message-bubble ol{margin:8px 0;padding-left:22px}
.message-bubble li{margin:3px 0}
.message-bubble blockquote{border-left:3px solid var(--green);margin:8px 0;padding:4px 12px;color:var(--muted);background:var(--panel2);border-radius:0 6px 6px 0}
.message-bubble table{border-collapse:collapse;margin:8px 0;font-size:12px;width:100%}
.message-bubble th,.message-bubble td{border:1px solid var(--border);padding:6px 10px;text-align:left}
.message-bubble th{background:var(--panel2);font-weight:700}
.message-bubble code{background:var(--panel2);padding:1px 5px;border-radius:4px;font:12px 'DM Mono',monospace;color:var(--text);border:1px solid var(--border)}
.message-bubble pre{background:#1e1e2e!important;padding:14px 42px 14px 14px;border-radius:10px;overflow-x:auto;margin:10px 0;border:1px solid var(--border);position:relative;font-size:12px;line-height:1.5}
.message-bubble pre code{background:none!important;padding:0;border:0;font:12px 'DM Mono',monospace;text-shadow:none!important;color:#e4efe8}
.message-bubble a{color:var(--green);text-decoration:underline}
.message-bubble strong{font-weight:700}
.message-bubble em{font-style:italic}
.message-bubble hr{border:0;border-top:1px solid var(--border);margin:14px 0}

.msg-files{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;padding-top:8px;border-top:1px dashed var(--border)}
.msg-file-chip{display:flex;align-items:center;gap:4px;font-size:10px;background:var(--panel2);border:1px solid var(--border);padding:3px 8px;border-radius:12px;font-family:'DM Mono',monospace;color:var(--muted)}
.msg-file-chip::before{content:'📎';font-size:10px}

.code-copy-btn{position:absolute;top:8px;right:8px;background:rgba(255,255,255,.1);color:#e4efe8;border:1px solid rgba(255,255,255,.15);width:28px;height:28px;border-radius:6px;cursor:pointer;font-size:12px;display:grid;place-items:center;opacity:.6;transition:opacity .15s}
pre:hover .code-copy-btn{opacity:1}
.code-copy-btn:hover{background:var(--green);color:var(--bg)}

.thinking-block{margin-top:8px;padding:8px 12px;border-radius:8px;background:color-mix(in srgb,var(--green) 8%,transparent);border:1px dashed var(--green);font-size:12px}
.thinking-block summary{cursor:pointer;font-weight:600;color:var(--green);font-size:11px;display:flex;align-items:center;gap:6px}
.thinking-block summary .copy-think-btn{border:0;background:transparent;color:var(--green);opacity:.6;cursor:pointer;padding:2px;font-size:12px}
.thinking-block summary .copy-think-btn:hover{opacity:1}
.thinking-block pre{margin:8px 0 0;white-space:pre-wrap;font-family:inherit;font-size:11px;color:var(--muted);line-height:1.5;max-height:300px;overflow-y:auto;background:transparent!important;padding:0!important;border:0!important}

.meta-line{margin-top:8px;display:flex;gap:5px;flex-wrap:wrap;align-items:center;font-size:9px;color:var(--muted);font-family:'DM Mono',monospace}
.meta-line .tag{padding:1px 6px;border-radius:8px;font-weight:700;font-size:8px;text-transform:uppercase}
.meta-line .tag.ollama{background:#10b981;color:#fff}
.meta-line .tag.qwen{background:#ef4444;color:#fff}
.meta-line .tag.gemini{background:#8b5cf6;color:#fff}
.meta-line .tag.groq{background:#f59e0b;color:#000}
.meta-line .tag.err{background:#6b7280;color:#fff;opacity:.65;text-decoration:line-through}

.message-actions{display:flex;gap:2px;opacity:0;transition:opacity .15s;flex-shrink:0;align-self:center}
.message:hover .message-actions{opacity:1}
.message-action-btn{border:1px solid var(--border);background:var(--panel);width:26px;height:26px;border-radius:6px;display:grid;place-items:center;color:var(--muted);font-size:11px;cursor:pointer}
.message-action-btn:hover{color:var(--green);border-color:var(--green)}
.message-action-btn.danger:hover{color:var(--red);border-color:var(--red)}

.msg-time{position:absolute;bottom:-16px;font-size:9px;color:var(--muted);opacity:0;transition:opacity .15s;font-family:'DM Mono',monospace;white-space:nowrap}
.message:hover .msg-time{opacity:.7}
.message.user .msg-time{right:6px}

.edit-inline{width:100%;padding:10px;background:var(--bg);color:var(--text);border:1px solid var(--green);border-radius:8px;font-family:inherit;font-size:13px;line-height:1.6;resize:vertical;min-height:60px;margin-bottom:6px}
.edit-actions{display:flex;gap:6px;justify-content:flex-end;margin-top:6px}
.edit-actions button{padding:4px 10px;border-radius:6px;border:1px solid var(--border);background:var(--panel);color:var(--text);font-size:11px;font-weight:600;cursor:pointer}
.edit-actions button.primary{background:var(--green);color:var(--bg);border-color:var(--green)}

.message.typing .message-bubble{display:flex;gap:5px;align-items:center;padding:16px 18px}
.typing-dot{width:6px;height:6px;border-radius:50%;background:var(--muted);animation:typing 1.2s infinite ease-in-out}
.typing-dot:nth-child(2){animation-delay:.15s}
.typing-dot:nth-child(3){animation-delay:.3s}
@keyframes typing{0%,60%,100%{transform:translateY(0);opacity:.4}30%{transform:translateY(-4px);opacity:1}}

.live-thinking{margin-top:8px;padding:8px 12px;border-radius:8px;background:color-mix(in srgb,var(--green) 5%,transparent);border:1px dashed var(--green);font-size:12px;color:var(--muted);font-style:italic;white-space:pre-wrap;max-height:150px;overflow-y:auto}

.quick-prompts{display:flex;gap:6px;overflow-x:auto;padding:14px 0 12px;margin-top:auto;scrollbar-width:none;z-index:1}
.quick-prompts::-webkit-scrollbar{display:none}
.quick-prompt{flex-shrink:0;padding:7px 13px;border:1.5px dashed var(--border);border-radius:20px;background:var(--panel);color:var(--muted);font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:5px}
.quick-prompt:hover{border-color:var(--green);border-style:solid;color:var(--green);background:var(--green-light)}

.composer-area{background:var(--panel);border-top:1px solid var(--border);padding:10px clamp(15px,4vw,55px);display:flex;flex-direction:column;gap:10px;overflow:hidden;position:relative}
.composer-layout{display:flex;gap:8px;align-items:stretch;width:100%;min-width:0}
.composer-box{border:1px solid var(--border);border-radius:14px;background:var(--panel2);padding:10px 12px;display:flex;flex-direction:column;min-height:85px;gap:8px;flex:1;overflow:hidden;min-width:0;transition:border-color .2s, background .2s;position:relative}
.composer-box.dragging{border-color:var(--green);background:color-mix(in srgb,var(--green) 8%,var(--panel2));border-style:dashed}
.composer-box.dragging::after{content:'📎 Suelta el archivo aquí';position:absolute;inset:0;display:grid;place-items:center;background:color-mix(in srgb,var(--green) 15%,transparent);border-radius:14px;font-weight:700;color:var(--green);font-size:14px;pointer-events:none}

.pending-files{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:4px}
.pending-file{display:flex;align-items:center;gap:5px;background:var(--green-light);border:1px solid var(--green);padding:4px 8px;border-radius:12px;font-size:11px;color:var(--green);font-weight:600;font-family:'DM Mono',monospace;max-width:100%}
.pending-file .file-name{max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.pending-file .file-size{font-size:9px;opacity:.7;font-weight:400}
.pending-file .file-remove{border:0;background:transparent;color:var(--green);cursor:pointer;font-size:13px;padding:0 2px;line-height:1}

.composer-main{display:flex;flex:1;gap:8px;min-height:0;align-items:flex-start;min-width:0}
.modes-table{border-collapse:separate;border-spacing:0;margin:0;flex-shrink:0}
.modes-table td{padding:2px 0}
.modes-table .mode.icon-mode{display:flex;align-items:center;justify-content:center;width:36px;height:34px;padding:0;border:1px solid var(--border);border-radius:8px;background:var(--panel);color:var(--muted);font-size:16px;position:relative}
.modes-table .mode.icon-mode:hover{border-color:var(--green)}
.modes-table .mode.icon-mode.active{background:var(--green-light);color:var(--green);border-color:var(--green)}

.mode[data-info]{position:relative}
.mode[data-info]:hover::after{content:attr(data-info);position:absolute;left:calc(100% + 10px);top:50%;transform:translateY(-50%);background:var(--panel);border:1px solid var(--green);color:var(--text);padding:6px 10px;border-radius:8px;font-size:11px;font-weight:500;line-height:1.4;white-space:normal;width:max-content;max-width:240px;box-shadow:var(--shadow);z-index:100;pointer-events:none;animation:fadeIn .15s ease}
@media (max-width:650px){.mode[data-info]:hover::after{display:none}}

.composer-box textarea{border:0;outline:0;background:transparent;color:var(--text);resize:none;flex:1;width:100%;min-width:0;min-height:52px;max-height:280px;font-size:13px;line-height:1.6;overflow-y:auto;padding:6px 0;box-sizing:border-box;word-wrap:break-word;overflow-wrap:break-word;white-space:pre-wrap;display:block}

.composer-bottom{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}
.composer-hint{font-size:9px;color:var(--muted);flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.composer-buttons{display:flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:flex-end}
.ctx-indicator{display:flex;align-items:center;gap:4px;font:10px 'DM Mono',monospace;color:var(--muted)}
.ctx-bar{width:36px;height:6px;background:var(--border);border-radius:3px;overflow:hidden;position:relative}
.ctx-fill{height:100%;background:var(--green);border-radius:3px;transition:width .2s,background .2s}
.ctx-fill.warn{background:var(--yellow)}
.ctx-fill.danger{background:var(--red)}
.char-count{font:10px 'DM Mono',monospace;color:var(--muted);margin-right:2px}
.send-button,.voice-button,.camera-button,.attach-button{border:1px solid var(--border);border-radius:9px;font-size:11px;font-weight:700;background:var(--panel);color:var(--green);padding:8px 12px}
.send-button{background:var(--green);color:var(--bg);border-color:var(--green);padding:8px 17px}
.send-button.stop{background:var(--red);border-color:var(--red);color:#fff}
.send-button:disabled{opacity:.45;cursor:not-allowed}
.voice-button.recording{background:#fff0e4;color:#d76b22;border-color:#ffd4b3}
.attach-button.has-files{background:var(--green-light);border-color:var(--green)}
.think{border:1px solid var(--border);border-radius:9px;padding:7px 12px;font-size:11px;font-weight:700;background:var(--panel);color:var(--muted)}
.think.active{background:var(--green-light);color:var(--green);border-color:var(--green)}

/* Modales */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:100}
.modal-overlay.open{display:flex}
.modal-card{background:var(--panel);border:1px solid var(--border);border-radius:16px;padding:22px;width:min(480px,92vw);box-shadow:var(--shadow);max-height:90vh;overflow:auto}
.modal-card h3{margin:0 0 6px;font-size:16px}
.modal-card p.subtitle{margin:0 0 18px;font-size:11px;color:var(--muted)}
.shortcut-config-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);gap:12px}
.shortcut-config-row:last-of-type{border:0}
.shortcut-config-row > span{font-size:12px}
.shortcut-input{border:1px solid var(--border);background:var(--panel2);color:var(--text);padding:7px 12px;border-radius:8px;font:11px"DM Mono",monospace;font-weight:600;min-width:120px;text-align:center}
.shortcut-input:hover{border-color:var(--green)}
.shortcut-input.capturing{background:var(--green-light);border-color:var(--green);color:var(--green);animation:pulse 1s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.55}}
.modal-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:18px}
.modal-actions button{border:1px solid var(--border);background:var(--panel);color:var(--text);padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600}
.modal-actions button.primary{background:var(--green);color:var(--bg);border-color:var(--green)}
.modal-actions button:hover{border-color:var(--green)}
.setting-input{width:100%;padding:6px 10px;border-radius:6px;border:1px solid var(--border);background:var(--panel2);color:var(--text);font-size:11px;margin-top:4px}

/* Editor de prompts */
.prompt-edit-row{display:flex;gap:6px;align-items:flex-start;padding:10px 0;border-bottom:1px solid var(--border)}
.prompt-edit-row:last-child{border:0}
.prompt-edit-row .icon-input{width:44px;text-align:center;font-size:16px;padding:6px}
.prompt-edit-row .label-input{flex:0 0 110px}
.prompt-edit-row .prompt-input{flex:1;min-height:36px;font-family:'DM Mono',monospace;font-size:11px}
.prompt-edit-row .del-btn{background:transparent;border:1px solid var(--border);color:var(--red);width:32px;height:32px;border-radius:6px;cursor:pointer;flex-shrink:0;margin-top:4px}
.prompt-edit-row .del-btn:hover{background:var(--red);color:#fff;border-color:var(--red)}
@media (max-width:560px){
  .prompt-edit-row{flex-wrap:wrap}
  .prompt-edit-row .icon-input,.prompt-edit-row .label-input{flex:1}
  .prompt-edit-row .prompt-input{flex-basis:100%}
}

/* Modal layout */
.layout-columns{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:14px 0}
@media (max-width:600px){.layout-columns{grid-template-columns:1fr}}
.layout-col h4{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin:0 0 8px;font-weight:700}
.sortable-list{display:flex;flex-direction:column;gap:6px;min-height:40px;padding:6px;border:1px dashed var(--border);border-radius:8px}
.sortable-item{display:flex;align-items:center;gap:8px;padding:8px 10px;background:var(--panel2);border:1px solid var(--border);border-radius:8px;font-size:12px;cursor:grab;transition:border-color .15s}
.sortable-item:hover{border-color:var(--green)}
.sortable-item:active{cursor:grabbing}
.sortable-item .drag-handle{color:var(--muted);font-size:14px;user-select:none;letter-spacing:-2px}
.sortable-item .icon{font-size:14px}
.sortable-item .name{flex:1;font-weight:600}
.sortable-item .eye-btn{background:transparent;border:0;font-size:14px;cursor:pointer;padding:2px 4px;border-radius:4px;opacity:.4}
.sortable-item .eye-btn:hover{opacity:1;background:var(--border)}
.sortable-item .eye-btn.on{opacity:1}
.sortable-ghost{opacity:.4;background:var(--green-light)}
.sortable-chosen{box-shadow:var(--shadow)}

.cmdpal{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);display:none;align-items:flex-start;justify-content:center;padding-top:15vh;z-index:150}
.cmdpal.open{display:flex}
.cmdpal-box{background:var(--panel);border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);width:min(560px,92vw);overflow:hidden}
.cmdpal-input{width:100%;border:0;padding:16px 20px;font-size:15px;background:var(--panel);color:var(--text);outline:none;border-bottom:1px solid var(--border)}
.cmdpal-list{max-height:380px;overflow-y:auto;padding:6px}
.cmdpal-item{padding:10px 14px;border-radius:8px;cursor:pointer;display:flex;align-items:center;gap:10px;font-size:13px}
.cmdpal-item:hover,.cmdpal-item.active{background:var(--green-light);color:var(--green)}
.cmdpal-item .icon{font-size:16px;width:22px;text-align:center}
.cmdpal-item .title{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.cmdpal-item .hint{font-size:10px;color:var(--muted);font-family:'DM Mono',monospace}
.cmdpal-empty{padding:20px;text-align:center;color:var(--muted);font-size:12px}
.cmdpal-cat{font-size:9px;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);padding:10px 14px 4px;font-weight:700}

.meta-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:12px}
.meta-row:last-child{border:0}
.meta-row span:first-child{color:var(--muted)}
.meta-row span:last-child{font-family:'DM Mono',monospace;font-weight:600}

#cameraDialog{border:none;background:transparent;padding:0}
.camera-box{background:var(--panel);border:1px solid var(--border);border-radius:16px;padding:20px;text-align:center;box-shadow:var(--shadow)}
.camera-box video{width:min(480px,90vw);border-radius:8px;background:#000;display:block;margin:10px auto}
.camera-box .actions{margin-top:14px;display:flex;gap:10px;justify-content:center}
.camera-box .actions button{padding:8px 16px;border-radius:8px;font-weight:600;border:1px solid var(--border);background:var(--panel);color:var(--text);cursor:pointer}
.camera-box .actions button.primary{background:var(--green);color:var(--bg);border-color:var(--green)}

.toast-container{position:fixed;top:76px;right:20px;display:flex;flex-direction:column;gap:8px;z-index:200;pointer-events:none;max-height:calc(100vh - 100px)}
.toast{background:var(--panel);border:1px solid var(--border);border-left:3px solid var(--green);padding:10px 14px;border-radius:8px;box-shadow:var(--shadow);font-size:12px;pointer-events:auto;max-width:320px;animation:toastIn .22s ease}
.toast.error{border-left-color:var(--red)}
.toast.warn{border-left-color:var(--yellow)}
@keyframes toastIn{from{transform:translateX(20px);opacity:0}to{transform:translateX(0);opacity:1}}
@keyframes toastOut{from{transform:translateX(0);opacity:1}to{transform:translateX(20px);opacity:0}}

@media (max-width:1200px){.layout{grid-template-columns:var(--sidebar-w) minmax(0,1fr)}.right-sidebar{display:none}}
@media (max-width:850px){:root{--sidebar-w:275px}.sidebar{padding:14px 11px}.chat-history{padding:20px 12px 0}}
@media (max-width:650px){
  body{overflow:hidden}
  .topbar{padding:0 12px;height:60px}
  #mobileMenu{display:inline-flex}
  .sidebar-toggle-float{display:none!important}
  .layout{height:calc(100vh - 60px);grid-template-columns:1fr}
  .sidebar{position:fixed;z-index:15;left:0;top:60px;bottom:0;width:min(330px,88vw);transform:translateX(-105%);transition:.25s;box-shadow:var(--shadow)}
  .sidebar.open{transform:translateX(0)}
  .status{display:none}
  .chat-history{padding:16px 12px 0}
  .composer-area{padding:10px}
  .modes-table .mode.icon-mode{width:32px;height:30px;font-size:14px}
  .think{padding:6px 9px;font-size:10px}
  .chat-heading .session{display:none}
  .chat-minimap{display:none}
  .msg-time{display:none}
}
</style>
</head>
<body class="light">

<header class="topbar">
  <div class="brand">
    <button class="icon-button" id="mobileMenu" aria-label="Menú">☰</button>
    <div class="brand-icon">M</div>
    <div>Makka AI<small>MULTI-AGENT WORKSPACE</small></div>
  </div>
  <div class="top-actions">
    <div class="status"><span class="dot" id="statusDot"></span><span id="statusText">Comprobando…</span></div>
    <button class="power off" id="powerButton"><span id="powerIcon">○</span><span id="powerText">OFF</span></button>
    <div class="settings-wrap">
      <button class="icon-button" id="settingsButton" aria-label="Configuración">⚙</button>
      <div class="dropdown" id="settingsDropdown">
        <h3>Configuración</h3>
        <div class="setting-row">
          <span>Tema</span>
          <select class="setting-btn" id="themeSelect">
            <option value="light">Claro</option>
            <option value="dark">Oscuro</option>
            <option value="mint">Menta</option>
            <option value="lilac">Lilac</option>
          </select>
        </div>
        <div class="setting-row"><span>Atajos de teclado</span><button class="setting-btn" id="editShortcutsBtn">Editar</button></div>
        <div class="setting-row" style="flex-direction:column;align-items:flex-start;">
          <span>Prompts rápidos</span>
          <button class="setting-btn" id="editPromptsBtn" style="margin-top:6px;width:100%">Editar Prompts</button>
        </div>
        <div class="setting-row"><span>Submenús panel derecho</span><button class="setting-btn" id="editRightPanelBtn">Editar</button></div>
        <div class="setting-row"><span>Ordenar paneles</span><button class="setting-btn" id="editLayoutBtn">Editar</button></div>
        <div class="setting-row"><span>Comandos</span><button class="setting-btn" id="openCmdBtn">⌘+K</button></div>
      </div>
    </div>
  </div>
</header>

<main class="layout" id="layout">
  <button class="sidebar-toggle-float" id="sidebarToggle" aria-label="Mostrar u ocultar panel">
    <span id="sidebarToggleIcon">‹</span>
  </button>

  <aside class="sidebar" id="sidebar">
    <section class="section" data-section-id="sessions">
      <div class="side-heading" data-target="section-sessions">
        <div class="label"><strong>Conversaciones</strong></div>
        <div style="display:flex;align-items:center;gap:6px">
          <span id="sessionCount">0</span>
          <span class="chevron">▾</span>
        </div>
      </div>
      <div class="section-body" id="section-sessions">
        <div class="search-bar">
          <input type="search" id="searchInput" placeholder="Buscar en títulos y mensajes..." autocomplete="off">
        </div>
        <div class="conv-toolbar">
          <button id="newSessionBtn" title="Nueva conversación">＋ Nueva</button>
          <button id="newFolderBtn" title="Nueva carpeta">📁 Carpeta</button>
          <button id="selectModeBtn" title="Selección múltiple">☑ Multi</button>
        </div>
        <div class="multi-actions" id="multiActions">
          <span class="count" id="selectedCount">0 seleccionadas</span>
          <button id="multiPinBtn">📌 Pin</button>
          <button id="multiUnpinBtn">📌✕</button>
          <button id="multiMoveBtn">📁 Mover</button>
          <button id="multiDeleteBtn">🗑 Borrar</button>
          <button id="multiCancelBtn" class="cancel">Cancelar</button>
        </div>
        <div class="session-tree" id="sessionTree"></div>
      </div>
    </section>

    <section class="section" data-section-id="agents">
      <div class="side-heading" data-target="section-agents">
        <div class="label"><strong>Estado de agentes</strong></div>
        <div style="display:flex;align-items:center;gap:6px"><span>04 AGENTES</span><span class="chevron">▾</span></div>
      </div>
      <div class="section-body" id="section-agents">
        <article class="bot-card offline" data-agent="ollama">
          <div class="bot-top"><div class="bot-avatar">O</div><div><div class="bot-name">Ollama</div><div class="bot-model">mimo-v2.6:9b · local</div></div><span class="bot-state">● —</span></div>
          <div class="thought"><strong>Pensamiento:</strong> Modelo principal. 16k contexto.</div>
        </article>
        <article class="bot-card offline" data-agent="qwen">
          <div class="bot-top"><div class="bot-avatar">Q</div><div><div class="bot-name">Qwen Coder 7B</div><div class="bot-model">OpenRouter · código</div></div><span class="bot-state">● —</span></div>
          <div class="thought"><strong>Pensamiento:</strong> Especialista en código.</div>
        </article>
        <article class="bot-card offline" data-agent="gemini">
          <div class="bot-top"><div class="bot-avatar">G</div><div><div class="bot-name">Gemini 2.0 Flash</div><div class="bot-model">Google · análisis</div></div><span class="bot-state">● —</span></div>
          <div class="thought"><strong>Pensamiento:</strong> Razonamiento rápido.</div>
        </article>
        <article class="bot-card offline" data-agent="groq">
          <div class="bot-top"><div class="bot-avatar">X</div><div><div class="bot-name">Groq</div><div class="bot-model">llama-3.3-70b · veloz</div></div><span class="bot-state">● —</span></div>
          <div class="thought"><strong>Pensamiento:</strong> Respuestas ultra-rápidas.</div>
        </article>
      </div>
    </section>

    <section class="section" data-section-id="tokens">
      <div class="side-heading" data-target="section-tokens">
        <div class="label"><strong>Consumo de tokens</strong></div>
        <div style="display:flex;align-items:center;gap:6px"><span>ÚLTIMAS 6</span><span class="chevron">▾</span></div>
      </div>
      <div class="section-body" id="section-tokens">
        <div class="chart-wrap">
          <div class="metric-head"><strong>Total consumido</strong><span id="tokenTotal">0</span></div>
          <svg viewBox="0 0 270 75" preserveAspectRatio="none" id="tokenChart">
            <defs><linearGradient id="fill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="var(--green)" stop-opacity=".26"/><stop offset="100%" stop-color="var(--green)" stop-opacity="0"/></linearGradient></defs>
            <path id="tokenFill" d="M0 75 L270 75 Z" fill="url(#fill)"/>
            <path id="tokenPath" d="M0 75 L270 75" fill="none" stroke="var(--green)" stroke-width="2.5"/>
            <circle id="tokenDot" cx="270" cy="75" r="3.5" fill="var(--orange)"/>
          </svg>
          <div class="chart-labels"><span>S1</span><span>S2</span><span>S3</span><span>S4</span><span>S5</span><span>S6</span></div>
        </div>
        <div class="token-breakdown">
          <div class="token-row" data-prov="ollama"><span>Ollama</span><b>0</b></div>
          <div class="token-row" data-prov="qwen"><span>Qwen</span><b>0</b></div>
          <div class="token-row" data-prov="gemini"><span>Gemini</span><b>0</b></div>
          <div class="token-row" data-prov="groq"><span>Groq</span><b>0</b></div>
        </div>
      </div>
    </section>

    <section class="section" data-section-id="logs">
      <div class="side-heading" data-target="section-logs">
        <div class="label"><strong>Logs del servidor</strong></div>
        <div style="display:flex;align-items:center;gap:6px"><span><i class="dot"></i> LIVE</span><span class="chevron">▾</span></div>
      </div>
      <div class="section-body" id="section-logs">
        <div class="logs" id="logs"></div>
      </div>
    </section>
  </aside>

  <section class="workspace">
    <div class="chat-wrapper">
      <div class="chat-history" id="chatHistory">
        <div class="chat-heading">
          <div style="min-width:0">
            <h1 id="chatTitle" title="Click para metadatos · Doble clic para renombrar">Conversación principal</h1>
            <p>Orquesta tus agentes desde un solo espacio.</p>
          </div>
          <div class="chat-heading-actions">
            <button class="icon-action" id="sysPromptBtn" title="System Prompt">⚙</button>
            <button class="icon-action" id="toggleUserBtn" title="Mostrar/ocultar mensajes de usuario">👤</button>
            <button class="icon-action" id="toggleAiBtn" title="Mostrar/ocultar mensajes de IA">🤖</button>
            <button class="icon-action" id="focusModeBtn" title="Focus mode">🔍</button>
            <button class="icon-action" id="newChatBtn" title="Nueva conversación">＋</button>
            <button class="icon-action" id="exportChatBtn" title="Exportar (Markdown)">⬇</button>
            <button class="icon-action danger" id="clearChatBtn" title="Limpiar conversación">🗑</button>
            <span class="session" id="sessionLabel">—</span>
          </div>
        </div>
        <div id="sysPromptBannerWrap"></div>
        <div class="quick-prompts" id="quickPrompts"></div>
      </div>
      <div class="chat-minimap" id="chatMinimap"></div>
    </div>

    <section class="composer-area" id="composerArea">
      <div class="composer-layout">
        <table class="modes-table">
          <tr><td><button class="mode icon-mode active" data-mode="Jerárquico" data-info="🌳 Jerárquico · Si el texto supera 16k tokens, lo trocea, reparte entre los 4 modelos y Ollama compone el resumen final.">🧭</button></td></tr>
          <tr><td><button class="mode icon-mode" data-mode="Mejor" data-info="🏆 Mejor · Manda el mismo prompt a los 4 y Ollama hace de juez eligiendo la mejor respuesta.">🏆</button></td></tr>
          <tr><td><button class="mode icon-mode" data-mode="Mezclar" data-info="🔀 Mezclar · Manda el mismo prompt a los 4 y Ollama fusiona todas las respuestas en una sola.">🔀</button></td></tr>
          <tr><td><button class="mode icon-mode" data-mode="Fallback" data-info="🛟 Fallback · Solo Ollama; si falla, prueba Qwen → Gemini → Groq en cadena.">🛟</button></td></tr>
        </table>

        <div class="composer-box" id="composerBox">
          <div class="pending-files" id="pendingFiles"></div>
          <div class="composer-main">
            <textarea id="chatInput" autofocus placeholder="Escribe tu mensaje… (⌘+K comandos · arrastra archivos para adjuntar)"></textarea>
          </div>
          <div class="composer-bottom">
            <span class="composer-hint" id="composerHint">Shift + Enter para enviar</span>
            <div class="composer-buttons">
              <div class="ctx-indicator" title="Uso aproximado del context window">
                <div class="ctx-bar"><div class="ctx-fill" id="ctxFill" style="width:0%"></div></div>
                <span id="ctxLabel">0%</span>
              </div>
              <span class="char-count" id="charCount"></span>
              <button class="think" id="thinkButton">🧠 Think OFF</button>
              <button class="voice-button" id="voiceButton">🎙</button>
              <button class="camera-button" id="cameraButton" title="Tomar foto">📷</button>
              <button class="attach-button" id="attachBtn" title="Adjuntar archivo">📎</button>
              <input type="file" id="fileInput" multiple hidden accept=".txt,.md,.py,.js,.ts,.json,.html,.css,.csv,.log,.yaml,.yml,.xml,.sql,.sh">
              <button class="send-button" id="sendButton" disabled>Enviar ↗</button>
            </div>
          </div>
        </div>
      </div>
    </section>
  </section>

  <aside class="right-sidebar" id="rightSidebar">
    <section class="section" data-section-id="symbols" id="right-section-symbols">
      <div class="side-heading">
        <div class="label"><strong>Symbols</strong></div>
      </div>
      <p style="font-size:10px;color:var(--muted);margin:-6px 0 10px;">Símbolos detectados en el código o respuesta.</p>
      <div class="symbol-list" id="symbolList">
        <div class="tree-empty">Esperando código...</div>
      </div>
    </section>

    <section class="section" data-section-id="tokens" id="right-section-tokens" style="display:none">
      <div class="side-heading">
        <div class="label"><strong>Tokens</strong></div>
      </div>
      <p style="font-size:10px;color:var(--muted);margin:-6px 0 10px;">Desglose por proveedor.</p>
      <div class="token-breakdown">
        <div class="token-row" data-prov="ollama"><span>Ollama</span><b>0</b></div>
        <div class="token-row" data-prov="qwen"><span>Qwen</span><b>0</b></div>
        <div class="token-row" data-prov="gemini"><span>Gemini</span><b>0</b></div>
        <div class="token-row" data-prov="groq"><span>Groq</span><b>0</b></div>
      </div>
    </section>

    <section class="section" data-section-id="logs" id="right-section-logs" style="display:none">
      <div class="side-heading">
        <div class="label"><strong>Logs</strong></div>
      </div>
      <p style="font-size:10px;color:var(--muted);margin:-6px 0 10px;">Últimos eventos del servidor.</p>
      <div class="logs" id="rightLogs" style="max-height:200px"></div>
    </section>
  </aside>
</main>

<div class="modal-overlay" id="shortcutsModal">
  <div class="modal-card">
    <h3>Atajos de teclado</h3>
    <p class="subtitle">Haz clic en un atajo y pulsa la nueva combinación. Esc para cancelar.</p>
    <div class="shortcut-config-row"><span>Enfocar chat</span><button class="shortcut-input" data-action="search">Shift + K</button></div>
    <div class="shortcut-config-row"><span>Enviar mensaje</span><button class="shortcut-input" data-action="send">Shift + Enter</button></div>
    <div class="shortcut-config-row"><span>Alternar Think</span><button class="shortcut-input" data-action="think">Shift + I</button></div>
    <div class="shortcut-config-row"><span>Panel izquierdo</span><button class="shortcut-input" data-action="sidebar">Shift + B</button></div>
    <div class="shortcut-config-row"><span>Panel derecho</span><button class="shortcut-input" data-action="rightSidebar">Shift + N</button></div>
    <div class="shortcut-config-row"><span>Comandos</span><button class="shortcut-input" data-action="cmdk">Ctrl + K</button></div>
    <div class="modal-actions">
      <button id="shortcutsReset">Restaurar</button>
      <button class="primary" id="shortcutsClose">Cerrar</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="promptsModal">
  <div class="modal-card" style="width:min(720px,92vw)">
    <h3>Editar Prompts Rápidos</h3>
    <p class="subtitle">Modifica icono, etiqueta y prompt de cada botón. Añade o elimina prompts.</p>
    <div id="promptsEditorList"></div>
    <div class="row" style="margin-top:12px;">
      <button class="setting-btn" id="promptsAdd">＋ Añadir prompt</button>
    </div>
    <div class="modal-actions">
      <button id="promptsReset">Restaurar Default</button>
      <button class="primary" id="promptsSave">Guardar</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="sysPromptModal">
  <div class="modal-card">
    <h3>System Prompt</h3>
    <p class="subtitle">Define el comportamiento de esta conversación. Se enviará como primer mensaje al modelo en cada petición.</p>
    <textarea id="sysPromptText" class="setting-input" style="height:180px;resize:vertical;font-family:'DM Mono',monospace;font-size:12px;line-height:1.6;" placeholder="Ej: Eres un experto en Python que responde con código limpio y comentado..."></textarea>
    <div class="modal-actions">
      <button id="sysPromptClear">Borrar</button>
      <button id="sysPromptCancel">Cancelar</button>
      <button class="primary" id="sysPromptSave">Guardar</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="metaModal">
  <div class="modal-card">
    <h3>Información de la conversación</h3>
    <p class="subtitle">Metadatos y estadísticas.</p>
    <div id="metaContent"></div>
    <div class="modal-actions">
      <button id="metaRename">Renombrar</button>
      <button id="metaDuplicate">Duplicar</button>
      <button class="primary" id="metaClose">Cerrar</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="rightPanelModal">
  <div class="modal-card">
    <h3>Submenús del panel derecho</h3>
    <p class="subtitle">Elige qué secciones aparecen en el panel lateral derecho.</p>
    <div class="setting-row">
      <span>⚡ Symbols (símbolos de código)</span>
      <button class="switch-btn" data-right-section="symbols"><span></span></button>
    </div>
    <div class="setting-row">
      <span>💰 Tokens (desglose por proveedor)</span>
      <button class="switch-btn" data-right-section="tokens"><span></span></button>
    </div>
    <div class="setting-row">
      <span>📜 Logs (últimos eventos)</span>
      <button class="switch-btn" data-right-section="logs"><span></span></button>
    </div>
    <div class="modal-actions">
      <button class="primary" id="rightPanelClose">Cerrar</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="layoutModal">
  <div class="modal-card" style="width:min(680px,92vw)">
    <h3>Ordenar paneles</h3>
    <p class="subtitle">Arrastra para reordenar. Usa el ojo para mostrar/ocultar.</p>
    <div class="layout-columns">
      <div class="layout-col">
        <h4>Izquierdo</h4>
        <div class="sortable-list" id="leftSortable"></div>
      </div>
      <div class="layout-col">
        <h4>Derecho</h4>
        <div class="sortable-list" id="rightSortable"></div>
      </div>
    </div>
    <div class="modal-actions">
      <button id="layoutReset">Restaurar</button>
      <button class="primary" id="layoutClose">Cerrar</button>
    </div>
  </div>
</div>

<div class="cmdpal" id="cmdpal">
  <div class="cmdpal-box">
    <input type="text" class="cmdpal-input" id="cmdpalInput" placeholder="Escribe un comando o busca una conversación…" autocomplete="off">
    <div class="cmdpal-list" id="cmdpalList"></div>
  </div>
</div>

<dialog id="cameraDialog">
  <div class="camera-box">
    <h3 style="margin-top:0;">Tomar Foto</h3>
    <video id="cameraVideo" autoplay playsinline></video>
    <div class="actions">
      <button class="primary" id="snapBtn">📸 Capturar</button>
      <button id="closeCamBtn">Cerrar</button>
    </div>
  </div>
</dialog>

<div class="toast-container" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/marked@12.0.2/marked.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-typescript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-bash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-sql.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
const $ = id => document.getElementById(id);
marked.setOptions({ breaks: true, gfm: true });

const BASE = window.location.pathname.replace(/[^/]+$/, '');
const URL_CTL    = BASE + 'ai-control.php';
const URL_STREAM = BASE + 'ai-api/chat/stream';
const URL_HEALTH = BASE + 'ai-api/health';
const URL_LOGS   = BASE + 'ai-logs.php';

let aiRunning = false;
let thinkOn = false;
let selectedMode = 'Jerárquico';
let generating = false;
let abortController = null;
let sessions = [];
let folders = [];
let currentSessionId = null;
let searchQuery = '';
let multiSelectMode = false;
let selectedSessions = new Set();
let localLogs = [], serverLogs = [];
let pendingFiles = [];
let pendingPrompts = []; // para el editor de prompts

/* Visibilidad de mensajes */
let visibility = { user: true, ai: true };
try {
  const s = JSON.parse(localStorage.getItem('makka-vis') || 'null');
  if (s) visibility = { ...visibility, ...s };
} catch {}

/* Orden de paneles */
const LEFT_SECTIONS = ['sessions', 'agents', 'tokens', 'logs'];
const RIGHT_SECTIONS = ['symbols', 'tokens', 'logs'];
let leftOrder = [...LEFT_SECTIONS];
let rightOrder = [...RIGHT_SECTIONS];
try {
  const lo = JSON.parse(localStorage.getItem('makka-left-order') || 'null');
  const ro = JSON.parse(localStorage.getItem('makka-right-order') || 'null');
  if (Array.isArray(lo)) {
    const valid = lo.filter(k => LEFT_SECTIONS.includes(k));
    LEFT_SECTIONS.forEach(k => { if (!valid.includes(k)) valid.push(k); });
    leftOrder = valid;
  }
  if (Array.isArray(ro)) {
    const valid = ro.filter(k => RIGHT_SECTIONS.includes(k));
    RIGHT_SECTIONS.forEach(k => { if (!valid.includes(k)) valid.push(k); });
    rightOrder = valid;
  }
} catch {}

const RIGHT_SECTIONS_DEFAULT = { symbols: true, tokens: false, logs: false };
let rightSections = { ...RIGHT_SECTIONS_DEFAULT };
try {
  const saved = JSON.parse(localStorage.getItem('makka-right-sections') || 'null');
  if (saved) rightSections = { ...RIGHT_SECTIONS_DEFAULT, ...saved };
} catch {}

const DEFAULT_PROMPTS = [
  { icon:'✎', label:'Resumen', prompt:'Resume el siguiente texto en 3 puntos clave:\n\n' },
  { icon:'✎', label:'Paso a paso', prompt:'Explica paso a paso cómo funciona:\n\n' },
  { icon:'✎', label:'Comparar', prompt:'Compara las siguientes opciones, ventajas y desventajas:\n\n' },
  { icon:'✎', label:'Código', prompt:'Genera código limpio y comentado para:\n\n' },
  { icon:'✎', label:'Traducir', prompt:'Traduce al inglés manteniendo el tono:\n\n' },
  { icon:'✎', label:'Corregir', prompt:'Corrige la gramática y ortografía del siguiente texto:\n\n' },
  { icon:'✎', label:'Crítica', prompt:'Actúa como crítico y señala los puntos débiles de:\n\n' }
];
let currentPrompts = [];

function escapeHtml(s) {
  const d = document.createElement('div');
  d.textContent = String(s ?? '');
  return d.innerHTML;
}
function formatDate(ts) {
  if (!ts) return '';
  const d = new Date(ts), now = new Date();
  const diffDays = Math.floor((now - d) / 86400000);
  if (diffDays === 0) return 'hoy';
  if (diffDays === 1) return 'ayer';
  if (diffDays < 7) return ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'][d.getDay()];
  return `${d.getDate()}/${d.getMonth()+1}`;
}
function formatTime(ts) {
  if (!ts) return '';
  return new Date(ts).toLocaleTimeString('es-ES', {hour:'2-digit', minute:'2-digit'});
}
function formatFullDate(ts) {
  if (!ts) return '—';
  return new Date(ts).toLocaleString('es-ES');
}
function formatBytes(n) {
  if (n < 1024) return n + ' B';
  if (n < 1024*1024) return (n/1024).toFixed(1) + ' KB';
  return (n/1024/1024).toFixed(2) + ' MB';
}
function modeToStrategy(mode) {
  return ({'Jerárquico':'chunk_parallel','Mejor':'best_of','Mezclar':'consensus','Fallback':'fallback'})[mode] || 'chunk_parallel';
}
function highlightText(text, query) {
  if (!query) return escapeHtml(text);
  const safe = escapeHtml(text);
  const safeQ = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  try {
    return safe.replace(new RegExp('(' + safeQ + ')', 'gi'), '<mark>$1</mark>');
  } catch { return safe; }
}

/* ═══════════ INDEXEDDB ═══════════ */
const IDB_NAME = 'makka-db';
const IDB_STORE = 'data';
let idbInstance = null;
function idbOpen() {
  if (idbInstance) return Promise.resolve(idbInstance);
  return new Promise((resolve, reject) => {
    const req = indexedDB.open(IDB_NAME, 1);
    req.onupgradeneeded = () => {
      const db = req.result;
      if (!db.objectStoreNames.contains(IDB_STORE)) db.createObjectStore(IDB_STORE);
    };
    req.onsuccess = () => { idbInstance = req.result; resolve(idbInstance); };
    req.onerror = () => reject(req.error);
  });
}
async function idbGet(key) {
  const db = await idbOpen();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(IDB_STORE, 'readonly');
    const req = tx.objectStore(IDB_STORE).get(key);
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });
}
async function idbSet(key, value) {
  const db = await idbOpen();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(IDB_STORE, 'readwrite');
    const req = tx.objectStore(IDB_STORE).put(value, key);
    req.onsuccess = () => resolve();
    req.onerror = () => reject(req.error);
  });
}
async function compressToBlob(obj) {
  const json = JSON.stringify(obj);
  const blob = new Blob([json], { type: 'application/json' });
  if (typeof CompressionStream === 'undefined') return blob;
  const stream = blob.stream().pipeThrough(new CompressionStream('gzip'));
  return new Response(stream).blob();
}
async function decompressFromBlob(blob) {
  if (!(blob instanceof Blob)) return blob;
  if (typeof DecompressionStream === 'undefined') return JSON.parse(await blob.text());
  const stream = blob.stream().pipeThrough(new DecompressionStream('gzip'));
  return JSON.parse(await new Response(stream).text());
}

/* ═══════════ MARKDOWN ═══════════ */
function renderMarkdown(text, final = false) {
  if (!text) return '';
  let html;
  try { html = marked.parse(text); }
  catch { html = '<p>' + escapeHtml(text) + '</p>'; }
  const wrap = document.createElement('div');
  wrap.innerHTML = html;
  wrap.querySelectorAll('pre').forEach(pre => {
    if (pre.querySelector('.code-copy-btn')) return;
    pre.style.position = 'relative';
    const btn = document.createElement('button');
    btn.className = 'code-copy-btn';
    btn.type = 'button';
    btn.title = 'Copiar código';
    btn.textContent = '📋';
    btn.addEventListener('click', async (ev) => {
      ev.stopPropagation();
      const code = pre.querySelector('code');
      const t = code ? code.textContent : pre.textContent;
      const ok = await copyText(t);
      btn.textContent = ok ? '✅' : '❌';
      setTimeout(() => btn.textContent = '📋', 1200);
    });
    pre.appendChild(btn);
    if (final) {
      const codeEl = pre.querySelector('code');
      if (codeEl) {
        try {
          const langMatch = (codeEl.className || '').match(/language-(\w+)/);
          if (!langMatch) codeEl.classList.add('language-text');
          Prism.highlightElement(codeEl);
        } catch {}
      }
    }
  });
  return wrap.innerHTML;
}

/* ═══════════ TOASTS ═══════════ */
function toast(msg, type='info', duration=2400) {
  const c = $('toastContainer');
  if (c.children.length >= 3) c.firstChild?.remove();
  const el = document.createElement('div');
  el.className = 'toast ' + type;
  el.textContent = msg;
  c.appendChild(el);
  setTimeout(() => {
    el.style.animation = 'toastOut .2s ease';
    setTimeout(() => el.remove(), 200);
  }, duration);
}

/* ═══════════ LOGS ═══════════ */
function addLog(level, msg) {
  localLogs.push({ time: new Date().toLocaleTimeString('es-ES',{hour12:false}), level, msg });
  if (localLogs.length > 20) localLogs.shift();
  renderLogPanel();
}
async function fetchServerLogs() {
  try {
    const r = await fetch(URL_LOGS + '?n=60', {cache:'no-store'});
    const j = await r.json();
    serverLogs = j.ok ? (j.lines || []) : [];
  } catch { serverLogs = []; }
  renderLogPanel();
}
function renderLogPanel() {
  const c = $('logs'); if (!c) return;
  let html = '';
  if (serverLogs.length) {
    html += `<div class="log-section">─── servidor ───</div>`;
    html += serverLogs.map(line => {
      const lvl = /Traceback|ERROR/.test(line) ? 'ERR' : /WARN/.test(line) ? 'WARN' : 'INFO';
      const cls = lvl==='ERR'?'log-err':lvl==='WARN'?'log-warn':'log-ok';
      return `<div class="log-line"><span class="${cls}">[${lvl}]</span> ${escapeHtml(line)}</div>`;
    }).join('');
  }
  if (localLogs.length) {
    html += `<div class="log-section">─── local ───</div>`;
    html += localLogs.map(l => {
      const cls = l.level==='WARN'?'log-warn':l.level==='ERROR'?'log-err':'log-ok';
      return `<div class="log-line"><span class="log-time">${l.time}</span><span class="${cls}">[${l.level}]</span> ${escapeHtml(l.msg)}</div>`;
    }).join('');
  }
  if (!html) html = `<div class="log-line" style="opacity:.5">(sin logs)</div>`;
  c.innerHTML = html; c.scrollTop = c.scrollHeight;

  const rc = $('rightLogs');
  if (rc) {
    let mini = '';
    if (serverLogs.length) {
      mini = serverLogs.slice(-5).map(line => {
        const lvl = /Traceback|ERROR/.test(line) ? 'ERR' : /WARN/.test(line) ? 'WARN' : 'INFO';
        const cls = lvl==='ERR'?'log-err':lvl==='WARN'?'log-warn':'log-ok';
        const truncated = line.length > 60 ? line.slice(0,60) + '…' : line;
        return `<div class="log-line"><span class="${cls}">[${lvl}]</span> ${escapeHtml(truncated)}</div>`;
      }).join('');
    }
    if (!mini) mini = `<div class="log-line" style="opacity:.5">(sin logs)</div>`;
    rc.innerHTML = mini;
    rc.scrollTop = rc.scrollHeight;
  }
}

/* ═══════════ POWER ═══════════ */
async function checkAIStatus() {
  try {
    const r = await fetch(URL_CTL + '?action=status', {cache:'no-store'});
    const j = await r.json();
    setAIState(!!j.running);
  } catch { setAIState(false); }
}
function setAIState(on) {
  aiRunning = on;
  $('powerButton').classList.toggle('off', !on);
  $('powerText').textContent = on ? 'ON' : 'OFF';
  $('powerIcon').textContent = on ? '●' : '○';
  $('statusText').textContent = on ? 'Sistema operativo' : 'Sistema detenido';
  $('statusDot').style.background = on ? '#35B879' : '#89968F';
  updateSendButtonState();
}
$('powerButton').addEventListener('click', async () => {
  const action = aiRunning ? 'stop' : 'start';
  $('powerButton').disabled = true;
  try {
    const r = await fetch(URL_CTL + '?action=' + action, {cache:'no-store'});
    const j = await r.json();
    if (action === 'start') {
      setAIState(!!j.ok);
      addLog(j.ok?'INFO':'WARN', j.ok?'IA arrancada.':'No arrancó: '+(j.msg||''));
    } else { setAIState(false); addLog('WARN','IA detenida.'); }
  } catch(e){ toast('Error: '+e.message,'error'); }
  finally { $('powerButton').disabled = false; checkAgents(); }
});

/* ═══════════ AGENTES ═══════════ */
async function checkAgents() {
  try {
    const r = await fetch(URL_HEALTH, {cache:'no-store'});
    if (!r.ok) throw new Error('HTTP '+r.status);
    const j = await r.json();
    const p = j.providers || {};
    setAgentState('ollama', p.ollama !== false);
    setAgentState('qwen', !!p.qwen);
    setAgentState('gemini', !!p.gemini);
    setAgentState('groq', !!p.groq);
  } catch { ['ollama','qwen','gemini','groq'].forEach(a=>setAgentState(a,false)); }
}
function setAgentState(name, on) {
  const card = document.querySelector(`.bot-card[data-agent="${name}"]`);
  if (!card) return;
  card.classList.toggle('offline', !on);
  const s = card.querySelector('.bot-state');
  if (s) s.textContent = on ? '● Activo' : '● Sin config';
}

/* ═══════════ THEME ═══════════ */
function setTheme(theme) {
  document.body.className = theme
    + (document.body.classList.contains('focus-mode') ? ' focus-mode' : '')
    + (document.body.classList.contains('right-collapsed') ? ' right-collapsed' : '')
    + (!visibility.user ? ' hide-user-msgs' : '')
    + (!visibility.ai ? ' hide-ai-msgs' : '');
  localStorage.setItem('makka-theme', theme);
  if ($('themeSelect')) $('themeSelect').value = theme;
}
$('themeSelect').addEventListener('change', e => setTheme(e.target.value));
setTheme(localStorage.getItem('makka-theme') || 'light');

$('focusModeBtn').addEventListener('click', () => {
  document.body.classList.toggle('focus-mode');
  toast(document.body.classList.contains('focus-mode') ? 'Focus mode ON' : 'Focus mode OFF', 'info', 1200);
});

/* ═══════════ VISIBILIDAD MENSAJES ═══════════ */
function applyVisibility() {
  document.body.classList.toggle('hide-user-msgs', !visibility.user);
  document.body.classList.toggle('hide-ai-msgs', !visibility.ai);
  $('toggleUserBtn').classList.toggle('active', !visibility.user);
  $('toggleAiBtn').classList.toggle('active', !visibility.ai);
  $('toggleUserBtn').title = visibility.user ? 'Ocultar mensajes de usuario' : 'Mostrar mensajes de usuario';
  $('toggleAiBtn').title = visibility.ai ? 'Ocultar mensajes de IA' : 'Mostrar mensajes de IA';
  updateMinimapVisibility();
}
function toggleVisibility(role) {
  visibility[role] = !visibility[role];
  try { localStorage.setItem('makka-vis', JSON.stringify(visibility)); } catch {}
  applyVisibility();
  toast(`Mensajes de ${role === 'user' ? 'usuario' : 'IA'} ${visibility[role] ? 'visibles' : 'ocultos'}`, 'info', 1400);
}
$('toggleUserBtn').addEventListener('click', () => toggleVisibility('user'));
$('toggleAiBtn').addEventListener('click', () => toggleVisibility('ai'));

function updateMinimapVisibility() {
  document.querySelectorAll('#chatMinimap .mini-row').forEach(row => {
    const idx = +row.dataset.idx;
    const msg = getCurrentSession()?.messages[idx];
    if (!msg) return;
    const hidden = (msg.role === 'user' && !visibility.user) || (msg.role === 'assistant' && !visibility.ai);
    row.classList.toggle('hidden-role', hidden);
  });
}

/* ═══════════ PREFERENCIAS ═══════════ */
function savePrefs() { localStorage.setItem('makka-prefs', JSON.stringify({mode:selectedMode,think:thinkOn})); }
try {
  const p = JSON.parse(localStorage.getItem('makka-prefs')||'{}');
  if (p.mode) selectedMode = p.mode;
  if (typeof p.think === 'boolean') thinkOn = p.think;
} catch {}

document.querySelectorAll('.mode[data-mode]').forEach(b => {
  b.addEventListener('click', () => {
    document.querySelectorAll('.mode[data-mode]').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    selectedMode = b.dataset.mode;
    savePrefs();
    addLog('INFO', `Modo: ${selectedMode}`);
  });
  b.classList.toggle('active', b.dataset.mode === selectedMode);
});
function applyThinkUI() {
  $('thinkButton').textContent = thinkOn ? '🧠 Think ON' : '🧠 Think OFF';
  $('thinkButton').classList.toggle('active', thinkOn);
}
$('thinkButton').addEventListener('click', () => { thinkOn = !thinkOn; applyThinkUI(); savePrefs(); });
applyThinkUI();

/* ═══════════ PROMPTS (editor extendido) ═══════════ */
function loadPrompts() {
  const saved = localStorage.getItem('makka-quick-prompts');
  currentPrompts = saved ? JSON.parse(saved) : JSON.parse(JSON.stringify(DEFAULT_PROMPTS));
  renderQuickPrompts();
}
function renderQuickPrompts() {
  const c = $('quickPrompts'); if (!c) return;
  c.innerHTML = currentPrompts.map(p => `
    <button class="quick-prompt" data-prompt="${escapeHtml(p.prompt)}">
      <span class="icon">${escapeHtml(p.icon)}</span><span class="label">${escapeHtml(p.label)}</span>
    </button>
  `).join('');
}
function openPromptsModal() {
  $('settingsDropdown').classList.remove('open');
  pendingPrompts = JSON.parse(JSON.stringify(currentPrompts));
  renderPromptsEditor();
  $('promptsModal').classList.add('open');
}
function renderPromptsEditor() {
  const list = $('promptsEditorList');
  list.innerHTML = pendingPrompts.map((p, i) => `
    <div class="prompt-edit-row" data-idx="${i}">
      <input class="setting-input icon-input" data-field="icon" value="${escapeHtml(p.icon)}" placeholder="✎" maxlength="3">
      <input class="setting-input label-input" data-field="label" value="${escapeHtml(p.label)}" placeholder="Etiqueta">
      <textarea class="setting-input prompt-input" data-field="prompt" placeholder="Prompt…">${escapeHtml(p.prompt)}</textarea>
      <button type="button" class="del-btn" title="Eliminar">🗑</button>
    </div>
  `).join('');
  list.querySelectorAll('.prompt-edit-row').forEach(row => {
    const idx = +row.dataset.idx;
    row.querySelectorAll('[data-field]').forEach(input => {
      input.addEventListener('input', () => {
        pendingPrompts[idx][input.dataset.field] = input.value;
      });
    });
    row.querySelector('.del-btn').addEventListener('click', () => {
      pendingPrompts.splice(idx, 1);
      renderPromptsEditor();
    });
  });
}
$('editPromptsBtn').addEventListener('click', openPromptsModal);
$('promptsAdd').addEventListener('click', () => {
  pendingPrompts.push({ icon:'✎', label:'Nuevo', prompt:'Nuevo prompt:\n\n' });
  renderPromptsEditor();
  $('promptsEditorList').lastElementChild?.querySelector('.label-input')?.focus();
});
$('promptsSave').addEventListener('click', () => {
  currentPrompts = pendingPrompts.filter(p => p.label.trim() || p.prompt.trim());
  localStorage.setItem('makka-quick-prompts', JSON.stringify(currentPrompts));
  renderQuickPrompts();
  $('promptsModal').classList.remove('open');
  toast('Prompts guardados');
});
$('promptsReset').addEventListener('click', () => {
  if (!confirm('¿Restaurar los prompts por defecto? Se perderán tus cambios.')) return;
  pendingPrompts = JSON.parse(JSON.stringify(DEFAULT_PROMPTS));
  currentPrompts = JSON.parse(JSON.stringify(DEFAULT_PROMPTS));
  localStorage.setItem('makka-quick-prompts', JSON.stringify(currentPrompts));
  renderPromptsEditor();
  renderQuickPrompts();
  toast('Prompts restaurados');
});
loadPrompts();

document.addEventListener('click', e => {
  const qp = e.target.closest('.quick-prompt'); if (!qp) return;
  const ta = $('chatInput');
  ta.value = qp.dataset.prompt;
  ta.focus();
  updateCharCount(); updateSendButtonState();
});

/* ═══════════ RIGHT PANEL SECTIONS ═══════════ */
function applyRightSections() {
  Object.keys(rightSections).forEach(key => {
    const el = document.getElementById('right-section-' + key);
    if (el) el.style.display = rightSections[key] ? '' : 'none';
  });
  document.querySelectorAll('[data-right-section]').forEach(btn => {
    const key = btn.dataset.rightSection;
    btn.classList.toggle('active', !!rightSections[key]);
  });
}
function toggleRightSection(key) {
  rightSections[key] = !rightSections[key];
  localStorage.setItem('makka-right-sections', JSON.stringify(rightSections));
  applyRightSections();
}
$('editRightPanelBtn').addEventListener('click', () => {
  $('settingsDropdown').classList.remove('open');
  applyRightSections();
  $('rightPanelModal').classList.add('open');
});
$('rightPanelClose').addEventListener('click', () => $('rightPanelModal').classList.remove('open'));
$('rightPanelModal').addEventListener('click', e => { if (e.target === $('rightPanelModal')) $('rightPanelModal').classList.remove('open'); });
document.querySelectorAll('[data-right-section]').forEach(btn => {
  btn.addEventListener('click', () => toggleRightSection(btn.dataset.rightSection));
});
applyRightSections();

/* ═══════════ LAYOUT ORDER (drag & drop) ═══════════ */
function applyLeftOrder() {
  const aside = document.querySelector('.sidebar');
  if (!aside) return;
  leftOrder.forEach(id => {
    const el = aside.querySelector(`[data-section-id="${id}"]`);
    if (el) aside.appendChild(el);
  });
}
function applyRightOrder() {
  const aside = document.querySelector('.right-sidebar');
  if (!aside) return;
  rightOrder.forEach(id => {
    const el = aside.querySelector(`[data-section-id="${id}"]`);
    if (el) aside.appendChild(el);
  });
}
let leftSortableInst = null, rightSortableInst = null;
const LEFT_LABELS = { sessions:['💬','Conversaciones'], agents:['🤖','Agentes'], tokens:['💰','Tokens'], logs:['📜','Logs'] };
const RIGHT_LABELS = { symbols:['⚡','Symbols'], tokens:['💰','Tokens'], logs:['📜','Logs'] };

function buildLayoutLists() {
  $('leftSortable').innerHTML = leftOrder.map(k => `
    <div class="sortable-item" data-key="${k}">
      <span class="drag-handle">⋮⋮</span>
      <span class="icon">${LEFT_LABELS[k][0]}</span>
      <span class="name">${LEFT_LABELS[k][1]}</span>
    </div>
  `).join('');
  $('rightSortable').innerHTML = rightOrder.map(k => `
    <div class="sortable-item" data-key="${k}">
      <span class="drag-handle">⋮⋮</span>
      <span class="icon">${RIGHT_LABELS[k][0]}</span>
      <span class="name">${RIGHT_LABELS[k][1]}</span>
      <button class="eye-btn ${rightSections[k]?'on':''}" data-toggle="${k}" title="Mostrar/ocultar">${rightSections[k]?'👁':'👁‍🗨'}</button>
    </div>
  `).join('');
  $('rightSortable').querySelectorAll('.eye-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      toggleRightSection(btn.dataset.toggle);
      btn.textContent = rightSections[btn.dataset.toggle] ? '👁' : '👁‍🗨';
      btn.classList.toggle('on', rightSections[btn.dataset.toggle]);
    });
  });
}
function openLayoutModal() {
  $('settingsDropdown').classList.remove('open');
  buildLayoutLists();
  $('layoutModal').classList.add('open');
  if (leftSortableInst) leftSortableInst.destroy();
  if (rightSortableInst) rightSortableInst.destroy();
  leftSortableInst = new Sortable($('leftSortable'), {
    animation: 150,
    handle: '.drag-handle',
    ghostClass: 'sortable-ghost',
    chosenClass: 'sortable-chosen',
    onEnd: () => {
      leftOrder = [...$('leftSortable').querySelectorAll('.sortable-item')].map(el => el.dataset.key);
      localStorage.setItem('makka-left-order', JSON.stringify(leftOrder));
      applyLeftOrder();
    }
  });
  rightSortableInst = new Sortable($('rightSortable'), {
    animation: 150,
    handle: '.drag-handle',
    ghostClass: 'sortable-ghost',
    chosenClass: 'sortable-chosen',
    onEnd: () => {
      rightOrder = [...$('rightSortable').querySelectorAll('.sortable-item')].map(el => el.dataset.key);
      localStorage.setItem('makka-right-order', JSON.stringify(rightOrder));
      applyRightOrder();
    }
  });
}
$('editLayoutBtn').addEventListener('click', openLayoutModal);
$('layoutClose').addEventListener('click', () => $('layoutModal').classList.remove('open'));
$('layoutModal').addEventListener('click', e => { if (e.target === $('layoutModal')) $('layoutModal').classList.remove('open'); });
$('layoutReset').addEventListener('click', () => {
  if (!confirm('¿Restaurar el orden por defecto?')) return;
  leftOrder = [...LEFT_SECTIONS];
  rightOrder = [...RIGHT_SECTIONS];
  localStorage.removeItem('makka-left-order');
  localStorage.removeItem('makka-right-order');
  applyLeftOrder();
  applyRightOrder();
  buildLayoutLists();
  toast('Orden restaurado');
});

/* ═══════════ SIDEBAR ═══════════ */
const layout = document.getElementById('layout');
function syncSidebarIcon() {
  const icon = $('sidebarToggleIcon');
  if (icon) icon.textContent = layout.classList.contains('sidebar-collapsed') ? '›' : '‹';
}
if (window.innerWidth > 650 && localStorage.getItem('makka-sidebar') === 'collapsed') layout.classList.add('sidebar-collapsed');
syncSidebarIcon();
function toggleSidebar() {
  if (window.innerWidth <= 650) $('sidebar').classList.toggle('open');
  else {
    layout.classList.toggle('sidebar-collapsed');
    localStorage.setItem('makka-sidebar', layout.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
    syncSidebarIcon();
  }
}
function toggleRightSidebar() {
  document.body.classList.toggle('right-collapsed');
  toast(!document.body.classList.contains('right-collapsed') ? 'Panel derecho ON' : 'Panel derecho OFF', 'info', 1200);
}
$('sidebarToggle').addEventListener('click', toggleSidebar);
$('mobileMenu').addEventListener('click', toggleSidebar);

/* ═══════════ SECCIONES PLEGABLES ═══════════ */
function refreshSectionHeight(bodyId) {
  const body = document.getElementById(bodyId);
  if (!body || body.classList.contains('collapsed')) return;
  body.style.maxHeight = body.scrollHeight + 'px';
}
document.querySelectorAll('.side-heading[data-target]').forEach(heading => {
  const targetId = heading.getAttribute('data-target');
  const body = document.getElementById(targetId);
  if (!body) return;
  const key = 'makka-sec-' + targetId;
  if (localStorage.getItem(key) === 'collapsed') {
    body.classList.add('collapsed'); heading.classList.add('collapsed'); body.style.maxHeight = '0';
  } else { body.style.maxHeight = body.scrollHeight + 'px'; }
  heading.addEventListener('click', () => {
    const isCollapsed = body.classList.toggle('collapsed');
    heading.classList.toggle('collapsed', isCollapsed);
    if (isCollapsed) { body.style.maxHeight = '0'; localStorage.setItem(key,'collapsed'); }
    else { body.style.maxHeight = body.scrollHeight + 'px'; localStorage.setItem(key,'expanded'); }
  });
});

/* ═══════════ AJUSTES ═══════════ */
$('settingsButton').addEventListener('click', () => $('settingsDropdown').classList.toggle('open'));
document.addEventListener('click', e => { if (!e.target.closest('.settings-wrap')) $('settingsDropdown').classList.remove('open'); });

/* ═══════════ SÍMBOLOS ═══════════ */
function updateSymbols(text) {
  const list = $('symbolList'); if (!list) return;
  const regex = /\b(function|const|let|var|class|def|async|await)\s+([a-zA-Z_][a-zA-Z0-9_]*)|([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/g;
  const skip = ['if','for','while','switch','return','new','typeof','instanceof','in','of','do','else','try','catch','print','len','str','int','float','list','dict','set','tuple'];
  const symbols = new Set();
  let m;
  while ((m = regex.exec(text)) !== null) {
    const name = m[2] || m[3];
    if (name && name.length > 1 && !skip.includes(name)) symbols.add(name);
  }
  if (!symbols.size) { list.innerHTML = '<div class="tree-empty">No se detectaron símbolos.</div>'; return; }
  list.innerHTML = Array.from(symbols).slice(0, 60).map(sym => `
    <div class="symbol-item" title="Copiar nombre"><span class="icon">⚡</span>${escapeHtml(sym)}<span class="type">fn</span></div>
  `).join('');
  list.querySelectorAll('.symbol-item').forEach(el => {
    el.addEventListener('click', async () => {
      const name = el.textContent.replace('⚡','').replace('fn','').trim();
      try { await navigator.clipboard.writeText(name); toast('Copiado: ' + name); } catch {}
    });
  });
}

/* ═══════════ DATA ═══════════ */
let _saveTimer = null;
let _pendingPayload = null;

function saveData() {
  _pendingPayload = { sessions, folders, current: currentSessionId };
  if (_saveTimer) clearTimeout(_saveTimer);
  _saveTimer = setTimeout(async () => {
    const data = _pendingPayload;
    _pendingPayload = null;
    try {
      const blob = await compressToBlob(data);
      await idbSet('makka-data', blob);
      try {
        localStorage.setItem('makka-index', JSON.stringify({
          sessionCount: sessions.length, current: currentSessionId, updatedAt: Date.now(),
        }));
      } catch {}
    } catch (e) {
      console.warn('[saveData] IndexedDB falló:', e);
      try { localStorage.setItem('makka-sessions', JSON.stringify(data)); } catch (e2) {
        toast('No se pudo guardar (almacenamiento lleno)', 'error');
      }
    }
  }, 120);
}

async function loadData() {
  let data = null;
  try {
    const blob = await idbGet('makka-data');
    if (blob) data = await decompressFromBlob(blob);
  } catch (e) { console.warn('[loadData] IndexedDB falló:', e); }
  if (!data) {
    try { data = JSON.parse(localStorage.getItem('makka-sessions') || 'null'); } catch {}
  }
  if (data) {
    if (Array.isArray(data.sessions)) sessions = data.sessions.map(s => ({
      ...s, folderId: s.folderId ?? null, pinned: !!s.pinned,
      systemPrompt: s.systemPrompt || '',
      createdAt: s.createdAt || Date.now(), updatedAt: s.updatedAt || s.createdAt || Date.now(),
      messages: Array.isArray(s.messages) ? s.messages : []
    }));
    if (Array.isArray(data.folders)) folders = data.folders;
    if (data.current) currentSessionId = data.current;
  }
  if (!sessions.length) createNewSession(false);
  if (!getCurrentSession()) currentSessionId = sessions[0].id;
}

function getCurrentSession() { return sessions.find(s => s.id === currentSessionId) || null; }

function createNewSession(notify = true) {
  const id = 'sess_' + Date.now().toString(36) + Math.random().toString(36).slice(2,6);
  sessions.push({
    id, name:'Conversación '+(sessions.length+1), folderId:null, pinned:false,
    systemPrompt: '', createdAt:Date.now(), updatedAt:Date.now(), messages:[],
  });
  currentSessionId = id;
  saveData();
  if (notify) { renderSessionTree(); renderMessages(); updateChatHeading(); updateSysPromptBanner(); toast('Nueva conversación'); }
  return sessions[sessions.length - 1];
}
function deleteSession(id) {
  if (sessions.length === 1) { toast('No puedes eliminar la única conversación','warn'); return; }
  sessions = sessions.filter(s => s.id !== id);
  if (currentSessionId === id) currentSessionId = sessions[sessions.length - 1].id;
  saveData(); renderSessionTree(); renderMessages(); updateChatHeading(); updateTokenChart(); updateSysPromptBanner();
  toast('Conversación eliminada');
}
function renameConversation(id) {
  const s = sessions.find(x => x.id === id); if (!s) return;
  const name = prompt('Nuevo nombre:', s.name);
  if (name === null) return;
  if (!name.trim()) return toast('El nombre no puede estar vacío', 'warn');
  s.name = name.trim();
  s.updatedAt = Date.now();
  saveData();
  renderSessionTree();
  updateChatHeading();
  toast('Conversación renombrada');
}
function duplicateSession(id) {
  const orig = sessions.find(s => s.id === id); if (!orig) return;
  const copy = JSON.parse(JSON.stringify(orig));
  copy.id = 'sess_' + Date.now().toString(36) + Math.random().toString(36).slice(2,6);
  copy.name = orig.name + ' (copia)';
  copy.createdAt = Date.now(); copy.updatedAt = Date.now(); copy.pinned = false;
  sessions.push(copy);
  currentSessionId = copy.id;
  saveData(); renderSessionTree(); renderMessages(); updateChatHeading(); updateSysPromptBanner();
  toast('Conversación duplicada');
  $('metaModal').classList.remove('open');
}
function switchSession(id) {
  if (generating) stopGeneration();
  currentSessionId = id; saveData();
  renderMessages(); renderSessionTree(); updateChatHeading(); updateSysPromptBanner();
}

/* ═══════════ FOLDERS ═══════════ */
function createFolder() {
  const name = prompt('Nombre de la carpeta:', 'Nueva carpeta');
  if (!name?.trim()) return;
  folders.push({id:'fld_'+Date.now().toString(36), name:name.trim(), createdAt:Date.now()});
  saveData(); renderSessionTree(); refreshSectionHeight('section-sessions');
  toast('Carpeta creada');
}
function renameFolder(id) {
  const f = folders.find(x => x.id === id); if (!f) return;
  const name = prompt('Nuevo nombre:', f.name); if (!name?.trim()) return;
  f.name = name.trim(); saveData(); renderSessionTree();
  toast('Carpeta renombrada');
}
function deleteFolder(id) {
  const f = folders.find(x => x.id === id); if (!f) return;
  if (!confirm(`¿Eliminar la carpeta "${f.name}"? Las conversaciones se moverán a "Sin carpeta".`)) return;
  sessions.forEach(s => { if (s.folderId === id) s.folderId = null; });
  folders = folders.filter(x => x.id !== id);
  saveData(); renderSessionTree();
  toast('Carpeta eliminada');
}

/* ═══════════ MOVE MENU ═══════════ */
let activeMoveMenu = null;
function closeMoveMenu() {
  if (activeMoveMenu) { activeMoveMenu.remove(); activeMoveMenu = null; }
  document.removeEventListener('click', outsideMoveMenuHandler, true);
}
function outsideMoveMenuHandler(e) {
  if (activeMoveMenu && !activeMoveMenu.contains(e.target) && !e.target.closest('.move-btn') && !e.target.closest('#multiMoveBtn')) closeMoveMenu();
}
function openMoveMenu(anchor, ids) {
  closeMoveMenu();
  const menu = document.createElement('div');
  menu.className = 'folder-menu';
  menu.innerHTML = `<div class="folder-menu-item" data-folder="">📂 Sin carpeta</div>` +
    folders.map(f => `<div class="folder-menu-item" data-folder="${f.id}">📁 ${escapeHtml(f.name)}</div>`).join('') +
    `<div class="folder-menu-item new" data-folder="__new__">＋ Nueva carpeta…</div>`;
  document.body.appendChild(menu);
  const rect = anchor.getBoundingClientRect();
  menu.style.left = Math.min(rect.left, window.innerWidth - 200) + 'px';
  menu.style.top = Math.min(rect.bottom + 4, window.innerHeight - menu.offsetHeight - 10) + 'px';
  menu.addEventListener('click', e => {
    const item = e.target.closest('.folder-menu-item'); if (!item) return;
    const folderId = item.dataset.folder;
    if (folderId === '__new__') {
      const name = prompt('Nombre de la nueva carpeta:');
      if (name?.trim()) {
        const nid = 'fld_' + Date.now().toString(36);
        folders.push({id:nid, name:name.trim(), createdAt:Date.now()});
        sessions.forEach(s => { if (ids.has(s.id)) { s.folderId = nid; s.updatedAt = Date.now(); } });
        saveData(); renderSessionTree(); refreshSectionHeight('section-sessions');
        toast('Movidas a nueva carpeta');
      }
    } else {
      sessions.forEach(s => { if (ids.has(s.id)) { s.folderId = folderId || null; s.updatedAt = Date.now(); } });
      saveData(); renderSessionTree();
      toast('Conversaciones movidas');
    }
    closeMoveMenu(); exitMultiSelect();
  });
  activeMoveMenu = menu;
  setTimeout(() => document.addEventListener('click', outsideMoveMenuHandler, true), 0);
}

/* ═══════════ EXPORT MD ═══════════ */
function exportSessionToMD(s) {
  if (!s || !s.messages.length) return toast('No hay nada que exportar','warn');
  let md = `# ${s.name}\n\n`;
  md += `*Creado el: ${new Date(s.createdAt).toLocaleString()}*\n\n`;
  if (s.folderId) {
    const f = folders.find(x => x.id === s.folderId);
    if (f) md += `*Carpeta: ${f.name}*\n\n`;
  }
  if (s.systemPrompt) {
    md += `> **System Prompt:** ${s.systemPrompt.split('\n').join(' ')}\n\n`;
  }
  md += `---\n\n`;
  s.messages.forEach(m => {
    if (m.role === 'user') {
      md += `## 👤 Usuario\n\n${m.text}\n\n`;
      if (Array.isArray(m.files) && m.files.length) {
        md += `*Adjuntos: ${m.files.map(f => f.name).join(', ')}*\n\n`;
      }
    } else {
      md += `## 🤖 Makka AI\n\n`;
      if (m.thinking) md += `<details>\n<summary>🧠 Razonamiento</summary>\n\n${m.thinking}\n\n</details>\n\n`;
      md += `${m.text}\n\n`;
      if (m.strategy) md += `> *Modelo: ${m.strategy}${m.elapsed_ms ? ' · ' + m.elapsed_ms + 'ms' : ''}*\n\n`;
    }
  });
  const blob = new Blob([md], {type:'text/markdown;charset=utf-8'});
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = s.name.replace(/[^a-z0-9]/gi,'_').toLowerCase() + '.md';
  a.click();
  URL.revokeObjectURL(url);
  toast('Exportado a Markdown');
}

/* ═══════════ META MODAL ═══════════ */
$('chatTitle').addEventListener('click', () => {
  const s = getCurrentSession(); if (!s) return;
  const folder = folders.find(f => f.id === s.folderId);
  const assistantMsgs = s.messages.filter(m => m.role === 'assistant');
  const tokens = assistantMsgs.reduce((a,m) => a + (m.tokens_in||0) + (m.tokens_out||0), 0);
  $('metaContent').innerHTML = `
    <div class="meta-row"><span>Nombre</span><span>${escapeHtml(s.name)}</span></div>
    <div class="meta-row"><span>ID</span><span>${escapeHtml(s.id)}</span></div>
    <div class="meta-row"><span>Carpeta</span><span>${folder ? escapeHtml(folder.name) : '—'}</span></div>
    <div class="meta-row"><span>Pinned</span><span>${s.pinned ? 'Sí' : 'No'}</span></div>
    <div class="meta-row"><span>System Prompt</span><span>${s.systemPrompt ? 'Sí' : 'No'}</span></div>
    <div class="meta-row"><span>Creada</span><span>${formatFullDate(s.createdAt)}</span></div>
    <div class="meta-row"><span>Actualizada</span><span>${formatFullDate(s.updatedAt)}</span></div>
    <div class="meta-row"><span>Mensajes</span><span>${s.messages.length}</span></div>
    <div class="meta-row"><span>Tokens</span><span>${tokens.toLocaleString('es-ES')}</span></div>
  `;
  $('metaModal').classList.add('open');
});
// Doble clic en el título = renombrar
$('chatTitle').addEventListener('dblclick', () => {
  const s = getCurrentSession(); if (s) renameConversation(s.id);
});
$('metaClose').addEventListener('click', () => $('metaModal').classList.remove('open'));
$('metaRename').addEventListener('click', () => {
  const s = getCurrentSession(); if (!s) return;
  $('metaModal').classList.remove('open');
  setTimeout(() => renameConversation(s.id), 100);
});
$('metaDuplicate').addEventListener('click', () => {
  const s = getCurrentSession(); if (!s) return;
  duplicateSession(s.id);
});
$('metaModal').addEventListener('click', e => { if (e.target === $('metaModal')) $('metaModal').classList.remove('open'); });

/* ═══════════ SYSTEM PROMPT ═══════════ */
function updateSysPromptBanner() {
  const s = getCurrentSession();
  const wrap = $('sysPromptBannerWrap');
  if (!wrap) return;
  if (!s || !s.systemPrompt) { wrap.innerHTML = ''; $('sysPromptBtn').classList.remove('has-prompt'); return; }
  $('sysPromptBtn').classList.add('has-prompt');
  wrap.innerHTML = `
    <div class="sys-prompt-banner">
      <b>⚙ System Prompt</b>
      <span class="label">${escapeHtml(s.systemPrompt)}</span>
      <button id="sysPromptEditInline">Editar</button>
    </div>`;
  $('sysPromptEditInline').addEventListener('click', openSysPromptModal);
}
function openSysPromptModal() {
  const s = getCurrentSession(); if (!s) return;
  $('sysPromptText').value = s.systemPrompt || '';
  $('sysPromptModal').classList.add('open');
  setTimeout(() => $('sysPromptText').focus(), 50);
}
$('sysPromptBtn').addEventListener('click', openSysPromptModal);
$('sysPromptSave').addEventListener('click', () => {
  const s = getCurrentSession(); if (!s) return;
  s.systemPrompt = $('sysPromptText').value.trim();
  s.updatedAt = Date.now();
  saveData();
  updateSysPromptBanner();
  $('sysPromptModal').classList.remove('open');
  toast('System Prompt guardado');
});
$('sysPromptClear').addEventListener('click', () => {
  $('sysPromptText').value = '';
  const s = getCurrentSession(); if (!s) return;
  s.systemPrompt = '';
  saveData();
  updateSysPromptBanner();
  $('sysPromptModal').classList.remove('open');
  toast('System Prompt borrado', 'warn');
});
$('sysPromptCancel').addEventListener('click', () => $('sysPromptModal').classList.remove('open'));
$('sysPromptModal').addEventListener('click', e => { if (e.target === $('sysPromptModal')) $('sysPromptModal').classList.remove('open'); });

/* ═══════════ BÚSQUEDA GLOBAL ═══════════ */
function matchInSession(s, q) {
  if (!q) return { matches: 0 };
  const ql = q.toLowerCase();
  let matches = 0;
  for (const m of s.messages || []) {
    const t = (m.text || '').toLowerCase();
    let idx = 0;
    while ((idx = t.indexOf(ql, idx)) !== -1) { matches++; idx += ql.length; }
  }
  return { matches };
}

function getFilteredSessions() {
  const q = searchQuery.trim().toLowerCase();
  if (!q) return sessions.map(s => ({ session: s, matches: 0 }));
  const results = [];
  for (const s of sessions) {
    const nameHit = s.name.toLowerCase().includes(q);
    const msgInfo = matchInSession(s, q);
    if (nameHit || msgInfo.matches > 0) {
      results.push({ session: s, matches: msgInfo.matches });
    }
  }
  return results;
}

/* ═══════════ RENDER TREE ═══════════ */
function sortByUpdated(arr) { return [...arr].sort((a,b) => (b.updatedAt||0)-(a.updatedAt||0)); }

function renderSessionItem(s, matches = 0) {
  const div = document.createElement('div');
  const isActive = s.id === currentSessionId;
  const isSelected = selectedSessions.has(s.id);
  div.className = 'session-item' + (isActive?' active':'') + (s.pinned?' pinned':'') + (multiSelectMode?' multi':'') + (isSelected?' selected':'');
  div.dataset.sessionId = s.id;
  const q = searchQuery.trim();
  const nameHtml = q ? highlightText(s.name, q) : escapeHtml(s.name);
  const matchBadge = matches > 0 && !s.name.toLowerCase().includes(q.toLowerCase())
    ? `<span class="match-count" title="Coincidencias en mensajes">${matches}</span>`
    : '';
  div.innerHTML = `
    <span class="check">${isSelected ? '✓' : ''}</span>
    <span class="pin-btn" title="${s.pinned?'Quitar pin':'Pinear'}">📌</span>
    <span class="name" title="Doble clic para renombrar">${nameHtml}</span>
    ${matchBadge}
    <span class="date">${formatDate(s.createdAt)}</span>
    <button class="action-btn rename-btn" title="Renombrar">✎</button>
    <button class="action-btn move-btn" title="Mover a carpeta">📁</button>
    <button class="action-btn export-btn" title="Exportar MD">⬇</button>
    <button class="action-btn danger del-btn" title="Eliminar">✕</button>`;
  div.addEventListener('click', e => {
    if (e.target.classList.contains('del-btn')) { e.stopPropagation(); deleteSession(s.id); return; }
    if (e.target.classList.contains('pin-btn')) {
      e.stopPropagation();
      s.pinned = !s.pinned; s.updatedAt = Date.now();
      saveData(); renderSessionTree(); toast(s.pinned?'Pineada':'Despineada'); return;
    }
    if (e.target.classList.contains('rename-btn')) { e.stopPropagation(); renameConversation(s.id); return; }
    if (e.target.classList.contains('move-btn')) { e.stopPropagation(); openMoveMenu(e.target, new Set([s.id])); return; }
    if (e.target.classList.contains('export-btn')) { e.stopPropagation(); exportSessionToMD(s); return; }
    if (multiSelectMode) { toggleSelected(s.id); return; }
    switchSession(s.id);
  });
  div.addEventListener('dblclick', e => {
    if (e.target.classList.contains('name')) {
      e.stopPropagation();
      renameConversation(s.id);
    }
  });
  return div;
}

function renderGroup(container, {icon, name, items, folderId, showActions}) {
  const group = document.createElement('div'); group.className = 'tree-group';
  const header = document.createElement('div');
  const collapseKey = 'makka-group-' + (folderId === null ? 'unfiled' : folderId === '__pinned__' ? 'pinned' : folderId);
  const collapsed = localStorage.getItem(collapseKey) === '1';
  header.className = 'tree-group-header' + (collapsed ? ' collapsed' : '');
  header.innerHTML = `<span class="chev">▾</span><span class="icon">${icon}</span><span class="name">${escapeHtml(name)}</span><span class="badge">${items.length}</span>`;
  if (showActions) {
    const r = document.createElement('button'); r.className = 'group-btn'; r.textContent = '✎'; r.title = 'Renombrar';
    r.addEventListener('click', e => { e.stopPropagation(); renameFolder(folderId); });
    const d = document.createElement('button'); d.className = 'group-btn danger'; d.textContent = '✕'; d.title = 'Eliminar carpeta';
    d.addEventListener('click', e => { e.stopPropagation(); deleteFolder(folderId); });
    header.append(r, d);
  }
  const body = document.createElement('div');
  body.className = 'tree-group-body' + (collapsed ? ' collapsed' : '');
  header.addEventListener('click', e => {
    if (e.target.classList.contains('group-btn')) return;
    const nowCollapsed = !header.classList.contains('collapsed');
    header.classList.toggle('collapsed', nowCollapsed);
    body.classList.toggle('collapsed', nowCollapsed);
    localStorage.setItem(collapseKey, nowCollapsed ? '1' : '0');
    refreshSectionHeight('section-sessions');
  });
  if (!items.length) body.innerHTML = `<div class="tree-empty">Sin conversaciones</div>`;
  else sortByUpdated(items.map(x => x.session)).forEach(s => {
    const item = items.find(x => x.session.id === s.id);
    body.appendChild(renderSessionItem(s, item ? item.matches : 0));
  });
  group.append(header, body);
  container.appendChild(group);
}

function renderSessionTree() {
  const tree = $('sessionTree'); if (!tree) return;
  tree.innerHTML = '';
  const filtered = getFilteredSessions();
  const q = searchQuery.trim();

  const pinned = filtered.filter(x => x.session.pinned);
  if (pinned.length) renderGroup(tree, {icon:'📌', name:'Pineadas', items:pinned, folderId:'__pinned__', showActions:false});

  folders.forEach(f => {
    const inFolder = filtered.filter(x => x.session.folderId === f.id && !x.session.pinned);
    if (q && !inFolder.length) return;
    renderGroup(tree, {icon:'📁', name:f.name, items:inFolder, folderId:f.id, showActions:true});
  });

  const unfiled = filtered.filter(x => !x.session.pinned && (!x.session.folderId || !folders.find(f => f.id === x.session.folderId)));
  if (unfiled.length || !folders.length) renderGroup(tree, {icon:'📂', name:'Sin carpeta', items:unfiled, folderId:null, showActions:false});

  if (!filtered.length && q) tree.innerHTML = `<div class="tree-empty" style="text-align:center;padding:14px">Sin resultados para "${escapeHtml(q)}"</div>`;
  const count = $('sessionCount'); if (count) count.textContent = sessions.length;
  refreshSectionHeight('section-sessions');
}

/* ═══════════ SEARCH + MULTI ═══════════ */
$('searchInput').addEventListener('input', e => { searchQuery = e.target.value; renderSessionTree(); });

function enterMultiSelect() {
  multiSelectMode = true; selectedSessions.clear();
  $('selectModeBtn').classList.add('active');
  $('multiActions').classList.add('visible');
  updateSelectedCount(); renderSessionTree();
}
function exitMultiSelect() {
  multiSelectMode = false; selectedSessions.clear();
  $('selectModeBtn').classList.remove('active');
  $('multiActions').classList.remove('visible');
  renderSessionTree();
}
function toggleSelected(id) {
  if (selectedSessions.has(id)) selectedSessions.delete(id); else selectedSessions.add(id);
  updateSelectedCount(); renderSessionTree();
}
function updateSelectedCount() {
  $('selectedCount').textContent = `${selectedSessions.size} seleccionada${selectedSessions.size===1?'':'s'}`;
}
$('selectModeBtn').addEventListener('click', () => multiSelectMode ? exitMultiSelect() : enterMultiSelect());
$('multiPinBtn').addEventListener('click', () => {
  if (!selectedSessions.size) return toast('Nada seleccionado','warn');
  sessions.forEach(s => { if (selectedSessions.has(s.id)) { s.pinned = true; s.updatedAt = Date.now(); } });
  saveData(); renderSessionTree(); toast('Pineadas'); exitMultiSelect();
});
$('multiUnpinBtn').addEventListener('click', () => {
  if (!selectedSessions.size) return toast('Nada seleccionado','warn');
  sessions.forEach(s => { if (selectedSessions.has(s.id)) { s.pinned = false; s.updatedAt = Date.now(); } });
  saveData(); renderSessionTree(); toast('Despineadas'); exitMultiSelect();
});
$('multiMoveBtn').addEventListener('click', e => {
  if (!selectedSessions.size) return toast('Nada seleccionado','warn');
  openMoveMenu(e.currentTarget, new Set(selectedSessions));
});
$('multiDeleteBtn').addEventListener('click', () => {
  if (!selectedSessions.size) return toast('Nada seleccionado','warn');
  if (sessions.length - selectedSessions.size < 1) return toast('Debes conservar al menos una','warn');
  if (!confirm(`¿Eliminar ${selectedSessions.size} conversación(es)?`)) return;
  sessions = sessions.filter(s => !selectedSessions.has(s.id));
  if (!sessions.find(s => s.id === currentSessionId)) currentSessionId = sessions[0].id;
  saveData(); renderSessionTree(); renderMessages(); updateChatHeading(); updateTokenChart();
  toast('Conversaciones eliminadas'); exitMultiSelect();
});
$('multiCancelBtn').addEventListener('click', exitMultiSelect);

/* ═══════════ BOTONES ═══════════ */
$('newSessionBtn').addEventListener('click', () => { if (generating) stopGeneration(); createNewSession(); });
$('newFolderBtn').addEventListener('click', createFolder);
$('newChatBtn').addEventListener('click', () => { if (generating) stopGeneration(); createNewSession(); });
$('exportChatBtn').addEventListener('click', () => exportSessionToMD(getCurrentSession()));
$('clearChatBtn').addEventListener('click', () => {
  const s = getCurrentSession();
  if (!s || !s.messages.length) return toast('Ya está vacía','warn');
  if (!confirm('¿Borrar todos los mensajes de esta conversación?')) return;
  s.messages = []; saveData(); renderMessages(); updateTokenChart(); toast('Conversación limpiada');
});

/* ═══════════ CHAT HEADING ═══════════ */
function updateChatHeading() {
  const s = getCurrentSession(); if (!s) return;
  $('chatTitle').textContent = s.name;
  $('sessionLabel').textContent = 'SESIÓN ' + s.id.slice(-6).toUpperCase();
}

/* ═══════════ MINIMAP (historia numerada + copiar) ═══════════ */
function renderMinimap() {
  const minimap = $('chatMinimap'); if (!minimap) return;
  const s = getCurrentSession();
  if (!s) { minimap.innerHTML = ''; return; }
  minimap.innerHTML = s.messages.map((m,i) => `
    <div class="mini-row ${m.role}" data-idx="${i}">
      <button class="mini-num" title="Ir al mensaje ${i+1}">${i+1}</button>
      <button class="mini-copy" title="Copiar mensaje ${i+1}">📋</button>
    </div>
  `).join('');
  minimap.querySelectorAll('.mini-num').forEach(btn => {
    btn.addEventListener('click', () => {
      const idx = +btn.closest('.mini-row').dataset.idx;
      const el = document.querySelector(`.message[data-index="${idx}"]`);
      if (el && el.offsetParent !== null) {
        $('chatHistory').scrollTo({top: el.offsetTop - 30, behavior: 'smooth'});
      }
    });
  });
  minimap.querySelectorAll('.mini-copy').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.stopPropagation();
      const idx = +btn.closest('.mini-row').dataset.idx;
      const s = getCurrentSession(); if (!s) return;
      const msg = s.messages[idx]; if (!msg) return;
      const ok = await copyText(msg.text || '');
      btn.textContent = ok ? '✅' : '❌';
      setTimeout(() => btn.textContent = '📋', 1000);
      if (ok) toast('Mensaje ' + (idx+1) + ' copiado', 'info', 1200);
    });
  });
  updateMinimapVisibility();
}
let minimapScrollRAF = null;
$('chatHistory').addEventListener('scroll', () => {
  if (minimapScrollRAF) return;
  minimapScrollRAF = requestAnimationFrame(() => {
    minimapScrollRAF = null;
    const history = $('chatHistory');
    const scrollY = history.scrollTop + 100;
    const msgs = history.querySelectorAll('.message');
    let activeIdx = -1;
    msgs.forEach((msg, i) => {
      if (msg.offsetParent === null) return;
      if (msg.offsetTop <= scrollY) activeIdx = i;
    });
    document.querySelectorAll('#chatMinimap .mini-row').forEach((row, i) => {
      row.classList.toggle('active', i === activeIdx);
    });
  });
});

/* ═══════════ RENDER MESSAGE ═══════════ */
function renderMessage(msg, index) {
  const history = $('chatHistory');
  history.querySelector('.empty-chat')?.remove();

  const article = document.createElement('article');
  article.className = `message ${msg.role}`;
  article.dataset.index = index;

  const bubble = document.createElement('div');
  bubble.className = 'message-bubble';

  if (msg.role === 'assistant') {
    const contentDiv = document.createElement('div');
    contentDiv.className = 'msg-md';
    contentDiv.innerHTML = renderMarkdown(msg.text || '', true);
    bubble.appendChild(contentDiv);

    if (msg.thinking?.trim()) {
      const det = document.createElement('details');
      det.className = 'thinking-block';
      det.open = true;
      det.innerHTML = `
        <summary><span>🧠 Razonamiento (${msg.thinking.length} c.)</span>
        <button class="copy-think-btn" title="Copiar">📋</button></summary>
        <pre>${escapeHtml(msg.thinking)}</pre>`;
      bubble.appendChild(det);
    }

    if (msg.strategy) {
      const meta = document.createElement('div');
      meta.className = 'meta-line';
      let html = `<span>${escapeHtml(msg.strategy)}</span>`;
      if (msg.chunks) html += `<span>· ${msg.chunks} chunk${msg.chunks>1?'s':''}</span>`;
      if (msg.elapsed_ms) html += `<span>· ${msg.elapsed_ms}ms</span>`;
      if (Array.isArray(msg.providers)) {
        html += msg.providers.map(p => p.e
          ? `<span class="tag err" title="${escapeHtml(p.e)}">${escapeHtml(p.p)}</span>`
          : `<span class="tag ${escapeHtml(p.p)}">${escapeHtml(p.p)}</span>`).join('');
      }
      meta.innerHTML = html;
      bubble.appendChild(meta);
    }
  } else {
    bubble.textContent = msg.text;
    if (Array.isArray(msg.files) && msg.files.length) {
      const chips = document.createElement('div');
      chips.className = 'msg-files';
      chips.innerHTML = msg.files.map(f => `<span class="msg-file-chip">${escapeHtml(f.name)}</span>`).join('');
      bubble.appendChild(chips);
    }
    if (msg.image) {
      const img = document.createElement('img');
      img.src = msg.image;
      img.style.cssText = 'max-width:100%;border-radius:8px;margin-top:8px;display:block';
      bubble.appendChild(img);
    }
  }

  const time = document.createElement('span');
  time.className = 'msg-time';
  time.textContent = formatTime(msg.ts);
  bubble.appendChild(time);

  const actions = document.createElement('div');
  actions.className = 'message-actions';
  actions.innerHTML = `<button class="message-action-btn" data-action="copy" title="Copiar">📋</button>`;
  if (msg.role === 'user') actions.innerHTML += `<button class="message-action-btn" data-action="edit" title="Editar">✎</button>`;
  if (msg.role === 'assistant') actions.innerHTML += `<button class="message-action-btn" data-action="regenerate" title="Regenerar">↻</button>`;
  actions.innerHTML += `<button class="message-action-btn danger" data-action="delete" title="Eliminar">🗑</button>`;

  article.append(bubble, actions);
  const qp = history.querySelector('.quick-prompts');
  if (qp) history.insertBefore(article, qp); else history.appendChild(article);
  history.scrollTop = history.scrollHeight;
}
function renderMessages() {
  const history = $('chatHistory');
  history.querySelectorAll('.message, .empty-chat').forEach(el => el.remove());
  const s = getCurrentSession();
  if (!s || !s.messages.length) {
    const div = document.createElement('div');
    div.className = 'empty-chat';
    div.innerHTML = `<div class="mark">M</div><p>Inicia una conversación</p><small>Selecciona un prompt rápido o escribe abajo.</small>`;
    const qp = history.querySelector('.quick-prompts');
    if (qp) history.insertBefore(div, qp); else history.appendChild(div);
  } else {
    s.messages.forEach((m,i) => renderMessage(m,i));
  }
  renderMinimap();
}
function addMessage(role, text, extra = {}) {
  const s = getCurrentSession(); if (!s) return;
  s.messages.push({role, text, ts: Date.now(), ...extra});
  s.updatedAt = Date.now();
  if (s.messages.length === 1 && role === 'user' && s.name.startsWith('Conversación')) {
    s.name = text.slice(0,32) + (text.length>32?'…':'');
    updateChatHeading();
  }
  saveData();
  renderMessage(s.messages[s.messages.length-1], s.messages.length-1);
  renderMinimap(); renderSessionTree();
  updateContextIndicator();
}

/* ═══════════ COPY/DELETE/EDIT/REGEN ═══════════ */
async function copyText(text) {
  try { await navigator.clipboard.writeText(text); return true; }
  catch {
    const ta = document.createElement('textarea');
    ta.value = text; ta.style.cssText = 'position:fixed;left:-9999px;';
    document.body.appendChild(ta); ta.select();
    let ok = false; try { ok = document.execCommand('copy'); } catch {}
    ta.remove(); return ok;
  }
}
$('chatHistory').addEventListener('click', async e => {
  const copyThink = e.target.closest('.copy-think-btn');
  if (copyThink) {
    e.preventDefault(); e.stopPropagation();
    const pre = copyThink.closest('.thinking-block').querySelector('pre');
    if (pre) {
      const ok = await copyText(pre.textContent);
      toast(ok?'Razonamiento copiado':'No se pudo copiar', ok?'info':'error');
    }
    return;
  }
  const btn = e.target.closest('.message-action-btn'); if (!btn) return;
  const action = btn.dataset.action;
  const messageEl = btn.closest('.message');
  const index = +messageEl.dataset.index;
  const s = getCurrentSession(); if (!s) return;
  const msg = s.messages[index]; if (!msg) return;

  if (action === 'copy') {
    const ok = await copyText(msg.text);
    toast(ok?'Copiado':'No se pudo copiar', ok?'info':'error');
  } else if (action === 'delete') {
    s.messages.splice(index, 1); saveData(); renderMessages(); updateTokenChart(); toast('Mensaje eliminado');
  } else if (action === 'edit' && msg.role === 'user') {
    openEditInline(messageEl, index, msg);
  } else if (action === 'regenerate') {
    if (generating) return;
    s.messages.splice(index, 1); saveData(); renderMessages();
    sendToBackend();
  }
});

function openEditInline(messageEl, index, msg) {
  const bubble = messageEl.querySelector('.message-bubble');
  const originalHTML = bubble.innerHTML;
  bubble.innerHTML = '';

  const ta = document.createElement('textarea');
  ta.className = 'edit-inline';
  ta.value = msg.text;
  bubble.appendChild(ta);
  ta.focus();
  ta.setSelectionRange(ta.value.length, ta.value.length);

  const actions = document.createElement('div');
  actions.className = 'edit-actions';
  actions.innerHTML = `<button data-act="cancel">Cancelar</button><button data-act="save" class="primary">Guardar y regenerar</button>`;
  bubble.appendChild(actions);

  actions.querySelector('[data-act="cancel"]').addEventListener('click', () => {
    bubble.innerHTML = originalHTML;
    renderMessages();
  });
  actions.querySelector('[data-act="save"]').addEventListener('click', () => {
    const newText = ta.value.trim();
    if (!newText) return toast('No puede estar vacío','warn');
    const s = getCurrentSession();
    s.messages[index].text = newText;
    s.messages = s.messages.slice(0, index + 1);
    s.updatedAt = Date.now();
    saveData(); renderMessages();
    sendToBackend();
  });
}

/* ═══════════ ATTACH FILES ═══════════ */
async function handleFilesAdded(fileList) {
  const MAX_SIZE = 200 * 1024;
  const acceptedExts = ['.txt','.md','.py','.js','.ts','.json','.html','.css','.csv','.log','.yaml','.yml','.xml','.sql','.sh'];
  let added = 0, skipped = 0;
  for (const file of fileList) {
    const ext = '.' + (file.name.split('.').pop() || '').toLowerCase();
    if (!acceptedExts.includes(ext)) { skipped++; continue; }
    if (file.size > MAX_SIZE) { skipped++; continue; }
    try {
      const content = await new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsText(file);
      });
      pendingFiles.push({ name: file.name, content, size: file.size });
      added++;
    } catch (err) { skipped++; }
  }
  if (added) toast(`${added} archivo${added>1?'s':''} añadido${added>1?'s':''}`, 'info', 1500);
  if (skipped) toast(`${skipped} archivo${skipped>1?'s':''} ignorado${skipped>1?'s':''} (tipo o tamaño)`, 'warn', 1800);
  renderPendingFiles();
}

$('attachBtn').addEventListener('click', () => $('fileInput').click());
$('fileInput').addEventListener('change', async e => {
  const files = Array.from(e.target.files || []);
  $('fileInput').value = '';
  if (files.length) await handleFilesAdded(files);
});

function renderPendingFiles() {
  const wrap = $('pendingFiles');
  if (!wrap) return;
  if (!pendingFiles.length) { wrap.innerHTML = ''; $('attachBtn').classList.remove('has-files'); return; }
  $('attachBtn').classList.add('has-files');
  wrap.innerHTML = pendingFiles.map((f, i) => `
    <span class="pending-file" data-idx="${i}">
      <span class="file-name">${escapeHtml(f.name)}</span>
      <span class="file-size">${formatBytes(f.size)}</span>
      <button class="file-remove" title="Quitar">×</button>
    </span>
  `).join('');
  wrap.querySelectorAll('.file-remove').forEach(btn => {
    btn.addEventListener('click', () => {
      const idx = +btn.closest('.pending-file').dataset.idx;
      pendingFiles.splice(idx, 1);
      renderPendingFiles();
    });
  });
  updateSendButtonState();
}

/* ═══════════ DRAG & DROP ═══════════ */
const composerArea = $('composerArea');
const composerBox = $('composerBox');
let dragCounter = 0;

['dragenter', 'dragover'].forEach(evt => {
  document.addEventListener(evt, e => {
    if (!e.dataTransfer) return;
    const types = Array.from(e.dataTransfer.types || []);
    if (!types.includes('Files')) return;
    e.preventDefault();
    if (evt === 'dragenter') dragCounter++;
    composerBox.classList.add('dragging');
  });
});

document.addEventListener('dragleave', e => {
  if (!e.dataTransfer) return;
  const types = Array.from(e.dataTransfer.types || []);
  if (!types.includes('Files')) return;
  dragCounter--;
  if (dragCounter <= 0) { dragCounter = 0; composerBox.classList.remove('dragging'); }
});

document.addEventListener('drop', async e => {
  if (!e.dataTransfer) return;
  const types = Array.from(e.dataTransfer.types || []);
  if (!types.includes('Files')) return;
  e.preventDefault();
  dragCounter = 0;
  composerBox.classList.remove('dragging');
  const files = Array.from(e.dataTransfer.files || []);
  if (files.length) await handleFilesAdded(files);
});

/* ═══════════ GENERACIÓN ═══════════ */
function setGenerating(on) {
  generating = on;
  const btn = $('sendButton');
  if (on) { btn.textContent = 'Detener ■'; btn.classList.add('stop'); btn.disabled = false; }
  else    { btn.textContent = 'Enviar ↗'; btn.classList.remove('stop'); updateSendButtonState(); }
  document.querySelectorAll('.bot-card').forEach(c => c.classList.toggle('working', on && !c.classList.contains('offline')));
  document.querySelectorAll('.bot-state').forEach(el => {
    if (el.closest('.bot-card').classList.contains('offline')) return;
    el.textContent = on ? '● Trabajando' : '● Activo';
  });
}
function stopGeneration() {
  if (abortController) { abortController.abort(); abortController = null; }
  $('chatHistory').querySelector('.message.typing')?.remove();
  setGenerating(false);
  toast('Generación detenida','warn');
}

function buildApiMessages(s) {
  const msgs = [];
  if (s.systemPrompt && s.systemPrompt.trim()) {
    msgs.push({ role: 'system', content: s.systemPrompt.trim() });
  }
  for (const m of s.messages) {
    if (m.role !== 'user' && m.role !== 'assistant') continue;
    let content = m.text || '';
    if (m.role === 'user' && Array.isArray(m.files) && m.files.length) {
      const filesSection = m.files.map(f =>
        `\n\n--- ARCHIVO ADJUNTO: ${f.name} ---\n\`\`\`\n${f.content}\n\`\`\``
      ).join('\n');
      content = content + filesSection;
    }
    msgs.push({ role: m.role, content });
  }
  return msgs;
}

async function sendToBackend() {
  const s = getCurrentSession(); if (!s) return;
  const lastUser = [...s.messages].reverse().find(m => m.role === 'user');
  if (!lastUser) return;

  setGenerating(true);
  const historyEl = $('chatHistory');
  const article = document.createElement('article');
  article.className = 'message assistant typing';
  const bubble = document.createElement('div');
  bubble.className = 'message-bubble';
  bubble.innerHTML = `<div class="content-live"></div><div class="thinking-live"></div>`;
  article.append(bubble);
  const qp = historyEl.querySelector('.quick-prompts');
  if (qp) historyEl.insertBefore(article, qp); else historyEl.appendChild(article);
  historyEl.scrollTop = historyEl.scrollHeight;

  const contentDiv = bubble.querySelector('.content-live');
  const thinkingDiv = bubble.querySelector('.thinking-live');
  let fullContent = '', fullThinking = '';
  const apiMessages = buildApiMessages(s);
  const t0 = Date.now();
  abortController = new AbortController();

  let raf = null, pending = '';
  function scheduleRender() {
    if (raf) return;
    raf = requestAnimationFrame(() => {
      raf = null;
      contentDiv.innerHTML = renderMarkdown(pending, false);
      historyEl.scrollTop = historyEl.scrollHeight;
    });
  }

  try {
    const r = await fetch(URL_STREAM, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      signal: abortController.signal,
      body: JSON.stringify({messages:apiMessages, strategy:modeToStrategy(selectedMode), think:thinkOn})
    });
    if (!r.ok) throw new Error('HTTP ' + r.status);

    const reader = r.body.getReader();
    const decoder = new TextDecoder('utf-8');
    let buffer = '';

    while (true) {
      const {done, value} = await reader.read();
      if (done) break;
      buffer += decoder.decode(value, {stream:true});
      const lines = buffer.split('\n\n');
      buffer = lines.pop();

      for (const line of lines) {
        if (!line.startsWith('data: ')) continue;
        const dataStr = line.slice(6);
        if (!dataStr) continue;
        let data;
        try { data = JSON.parse(dataStr); } catch { continue; }
        if (data.error) throw new Error(data.error);

        if (data.thinking) {
          fullThinking += data.thinking;
          thinkingDiv.innerHTML = `<div class="live-thinking"><b>🧠 Pensando...</b><br>${escapeHtml(fullThinking)}</div>`;
          thinkingDiv.scrollTop = thinkingDiv.scrollHeight;
          historyEl.scrollTop = historyEl.scrollHeight;
        }
        if (data.content) {
          fullContent += data.content;
          pending = fullContent;
          scheduleRender();
        }
        if (data.done) {
          thinkingDiv.innerHTML = '';
          article.classList.remove('typing');
          contentDiv.innerHTML = renderMarkdown(fullContent, true);

          if (fullThinking.trim()) {
            const det = document.createElement('details');
            det.className = 'thinking-block';
            det.open = true;
            det.innerHTML = `
              <summary><span>🧠 Razonamiento (${fullThinking.length} c.)</span>
              <button class="copy-think-btn" title="Copiar">📋</button></summary>
              <pre>${escapeHtml(fullThinking)}</pre>`;
            bubble.appendChild(det);
          }

          const meta = document.createElement('div');
          meta.className = 'meta-line';
          let metaHtml = `<span>${escapeHtml(data.strategy || selectedMode)}</span>`;
          metaHtml += `<span>· ${Date.now() - t0}ms</span>`;

          const providersData = Array.isArray(data.results) ? data.results : [];
          if (providersData.length) {
            metaHtml += providersData.map(p => p.error
              ? `<span class="tag err" title="${escapeHtml(p.error)}">${escapeHtml(p.provider)}</span>`
              : `<span class="tag ${escapeHtml(p.provider)}">${escapeHtml(p.provider)}</span>`
            ).join('');
          } else {
            metaHtml += `<span class="tag ollama">ollama</span>`;
          }
          meta.innerHTML = metaHtml;
          bubble.appendChild(meta);

          const tokensIn = providersData.reduce((a, p) => a + (p.tokens_in || 0), 0);
          const tokensOut = providersData.reduce((a, p) => a + (p.tokens_out || 0), 0);
          let finalTokensIn = tokensIn, finalTokensOut = tokensOut;
          if (!finalTokensIn && !finalTokensOut) {
            const userChars = s.messages.filter(m => m.role === 'user').reduce((a, m) => a + (m.text||'').length, 0);
            finalTokensIn = Math.round(userChars / 4);
            finalTokensOut = Math.round(fullContent.length / 4);
          }

          s.messages.push({
            role: 'assistant',
            text: fullContent,
            thinking: fullThinking,
            strategy: data.strategy || selectedMode,
            elapsed_ms: Date.now() - t0,
            ts: Date.now(),
            tokens_in: finalTokensIn,
            tokens_out: finalTokensOut,
            providers: providersData.map(p => ({
              p: p.provider,
              e: p.error,
              ms: p.elapsed_ms,
              tokens_in: p.tokens_in || 0,
              tokens_out: p.tokens_out || 0,
            })),
          });
          saveData(); renderMinimap(); renderSessionTree();
          updateTokenChart(); updateSymbols(fullContent); updateContextIndicator();
        }
      }
    }
  } catch (e) {
    if (e.name === 'AbortError') return;
    contentDiv.innerHTML = '❌ Error: ' + escapeHtml(e.message);
    addLog('ERROR', 'Error backend: ' + e.message);
    toast('Error: ' + e.message,'error');
  } finally {
    abortController = null;
    setGenerating(false);
  }
}

/* ═══════════ SEND ═══════════ */
function updateSendButtonState() {
  const btn = $('sendButton'); if (!btn || generating) return;
  btn.disabled = !aiRunning || (!($('chatInput').value.trim()) && pendingFiles.length === 0);
}
function sendMessage() {
  const text = $('chatInput').value.trim();
  if ((!text && pendingFiles.length === 0) || !aiRunning || generating) {
    if (!aiRunning) toast('Enciende la IA primero','warn');
    return;
  }
  const files = pendingFiles.map(f => ({ name: f.name, content: f.content, size: f.size }));
  const finalText = text || (files.length ? 'Analiza el archivo adjunto.' : '');
  addMessage('user', finalText, files.length ? { files } : {});
  pendingFiles = [];
  renderPendingFiles();
  $('chatInput').value = '';
  autoResizeInput();
  updateCharCount(); updateSendButtonState();
  sendToBackend();
}
$('sendButton').addEventListener('click', () => generating ? stopGeneration() : sendMessage());

/* ═══════════ AUTO-RESIZE + CHAR COUNT + CONTEXT ═══════════ */
function autoResizeInput() {
  const ta = $('chatInput');
  if (!ta) return;
  ta.style.height = 'auto';
  const maxH = 280;
  const newH = Math.min(ta.scrollHeight, maxH);
  ta.style.height = newH + 'px';
  ta.style.overflowY = ta.scrollHeight > maxH ? 'auto' : 'hidden';
}
function updateCharCount() {
  const ta = $('chatInput'), el = $('charCount'); if (!el) return;
  const len = ta.value.length;
  el.textContent = len > 0 ? `${len}` : '';
  el.style.color = len > 500 ? 'var(--yellow)' : 'var(--muted)';
}
function updateContextIndicator() {
  const s = getCurrentSession(); if (!s) return;
  let tokens = 0;
  s.messages.forEach(m => { tokens += (m.tokens_in || 0) + (m.tokens_out || 0); });
  if (tokens === 0 && s.messages.length > 0) {
    const chars = s.messages.reduce((a, m) => a + (m.text||'').length, 0);
    tokens = Math.round(chars / 4);
  }
  const ctxMax = 16384;
  const pct = Math.min(100, Math.round((tokens / ctxMax) * 100));
  const fill = $('ctxFill'), label = $('ctxLabel');
  if (fill && label) {
    fill.style.width = pct + '%';
    fill.className = 'ctx-fill' + (pct > 85 ? ' danger' : pct > 60 ? ' warn' : '');
    label.textContent = pct + '%';
    label.title = `~${tokens.toLocaleString()} / ${ctxMax.toLocaleString()} tokens`;
  }
}
$('chatInput').addEventListener('input', () => {
  autoResizeInput();
  updateCharCount();
  updateSendButtonState();
});

/* ═══════════ TOKEN CHART ═══════════ */
function updateTokenChart() {
  const allMsgs = sessions.flatMap(s => s.messages.filter(m => m.role === 'assistant'));
  const totalIn  = allMsgs.reduce((a,m) => a + (m.tokens_in || 0), 0);
  const totalOut = allMsgs.reduce((a,m) => a + (m.tokens_out || 0), 0);
  const tt = $('tokenTotal'); if (tt) tt.textContent = (totalIn + totalOut).toLocaleString('es-ES');
  const perProv = {ollama:0, qwen:0, gemini:0, groq:0};
  allMsgs.forEach(m => {
    const provs = (m.providers || []).filter(p => p && !p.e && perProv.hasOwnProperty(p.p));
    if (!provs.length) return;
    const inPer = (m.tokens_in || 0)/provs.length;
    const outPer = (m.tokens_out || 0)/provs.length;
    provs.forEach(p => { perProv[p.p] += Math.round(inPer + outPer); });
  });
  document.querySelectorAll('.token-row').forEach(row => {
    const p = row.dataset.prov;
    if (p && row.querySelector('b')) row.querySelector('b').textContent = perProv[p].toLocaleString('es-ES');
  });
  const last6 = [...sessions].sort((a,b) => (b.updatedAt||0)-(a.updatedAt||0)).slice(0,6).reverse();
  const values = last6.map(s => {
    const ms = s.messages.filter(m => m.role === 'assistant');
    return ms.reduce((a,m) => a + (m.tokens_in||0) + (m.tokens_out||0), 0);
  });
  if (!values.length) values.push(0);
  const max = Math.max(...values, 1);
  const W = 270, H = 75;
  const step = values.length > 1 ? W / (values.length - 1) : W;
  let path = `M0 ${H - (values[0]/max)*H}`;
  values.forEach((v,i) => {
    const x = i * step, y = H - (v/max)*H;
    path += ` L${x.toFixed(1)} ${y.toFixed(1)}`;
  });
  const fillPath = `${path} L${W} ${H} L0 ${H} Z`;
  const tp = $('tokenPath'); if (tp) tp.setAttribute('d', path);
  const tf = $('tokenFill'); if (tf) tf.setAttribute('d', fillPath);
  const lastX = (values.length-1) * step;
  const lastY = H - (values[values.length-1]/max)*H;
  const td = $('tokenDot');
  if (td) { td.setAttribute('cx', lastX.toFixed(1)); td.setAttribute('cy', lastY.toFixed(1)); }
}

/* ═══════════ COMMAND PALETTE ═══════════ */
const CMD_ITEMS = [
  { id:'new',      icon:'➕', title:'Nueva conversación', hint:'', cat:'Acción', run:() => createNewSession() },
  { id:'newFolder',icon:'📁', title:'Nueva carpeta',      hint:'', cat:'Acción', run:() => createFolder() },
  { id:'focus',    icon:'🔍', title:'Toggle Focus Mode',  hint:'', cat:'Acción', run:() => $('focusModeBtn').click() },
  { id:'toggleUser', icon:'👤', title:'Toggle mensajes usuario', hint:'', cat:'Vista', run:() => toggleVisibility('user') },
  { id:'toggleAi',   icon:'🤖', title:'Toggle mensajes IA',     hint:'', cat:'Vista', run:() => toggleVisibility('ai') },
  { id:'sidebar',  icon:'👁', title:'Toggle Sidebar',     hint:'', cat:'Acción', run:() => toggleSidebar() },
  { id:'right',    icon:'👁', title:'Toggle Panel Derecho',hint:'', cat:'Acción', run:() => toggleRightSidebar() },
  { id:'layout',   icon:'⋮⋮', title:'Ordenar paneles',    hint:'', cat:'Acción', run:() => openLayoutModal() },
  { id:'rightSections', icon:'⚙', title:'Configurar submenús panel derecho', hint:'', cat:'Acción', run:() => $('editRightPanelBtn').click() },
  { id:'sysprompt',icon:'⚙', title:'Editar System Prompt',hint:'', cat:'Acción', run:() => openSysPromptModal() },
  { id:'rename',   icon:'✎', title:'Renombrar conversación actual', hint:'', cat:'Acción', run:() => { const s = getCurrentSession(); if (s) renameConversation(s.id); } },
  { id:'attach',   icon:'📎', title:'Adjuntar archivo',   hint:'', cat:'Acción', run:() => $('fileInput').click() },
  { id:'clear',    icon:'🗑', title:'Limpiar conversación',hint:'', cat:'Acción', run:() => $('clearChatBtn').click() },
  { id:'export',   icon:'⬇', title:'Exportar MD',         hint:'', cat:'Acción', run:() => $('exportChatBtn').click() },
  { id:'settings', icon:'⚙', title:'Abrir ajustes',       hint:'', cat:'Acción', run:() => $('settingsButton').click() },
  { id:'shortcuts',icon:'⌨', title:'Editar atajos',        hint:'', cat:'Acción', run:() => $('editShortcutsBtn').click() },
  { id:'prompts',  icon:'✎', title:'Editar prompts',       hint:'', cat:'Acción', run:() => $('editPromptsBtn').click() },
  { id:'theme-light', icon:'☀', title:'Tema Claro',        hint:'', cat:'Tema',   run:() => setTheme('light') },
  { id:'theme-dark',  icon:'🌙', title:'Tema Oscuro',      hint:'', cat:'Tema',   run:() => setTheme('dark') },
  { id:'theme-mint',  icon:'🍃', title:'Tema Menta',       hint:'', cat:'Tema',   run:() => setTheme('mint') },
  { id:'theme-lilac', icon:'🔮', title:'Tema Lilac',       hint:'', cat:'Tema',   run:() => setTheme('lilac') },
  { id:'mode-jer',  icon:'🧭', title:'Modo Jerárquico',    hint:'', cat:'Modo',   run:() => document.querySelector('.mode[data-mode="Jerárquico"]').click() },
  { id:'mode-mej',  icon:'🏆', title:'Modo Mejor',         hint:'', cat:'Modo',   run:() => document.querySelector('.mode[data-mode="Mejor"]').click() },
  { id:'mode-mez',  icon:'🔀', title:'Modo Mezclar',       hint:'', cat:'Modo',   run:() => document.querySelector('.mode[data-mode="Mezclar"]').click() },
  { id:'mode-fb',   icon:'🛟', title:'Modo Fallback',      hint:'', cat:'Modo',   run:() => document.querySelector('.mode[data-mode="Fallback"]').click() },
  { id:'think',     icon:'🧠', title:'Toggle Think',       hint:'', cat:'Modo',   run:() => $('thinkButton').click() },
];

let cmdActiveIdx = 0;
let cmdResults = [];

function openCmdPal() {
  $('cmdpal').classList.add('open');
  $('cmdpalInput').value = '';
  cmdActiveIdx = 0;
  renderCmdResults('');
  setTimeout(() => $('cmdpalInput').focus(), 30);
}
function closeCmdPal() { $('cmdpal').classList.remove('open'); }
$('openCmdBtn').addEventListener('click', () => { $('settingsDropdown').classList.remove('open'); openCmdPal(); });

function renderCmdResults(q) {
  const list = $('cmdpalList');
  q = q.trim().toLowerCase();
  const results = [];

  const filteredCmds = CMD_ITEMS.filter(c => !q || c.title.toLowerCase().includes(q) || c.cat.toLowerCase().includes(q));
  if (filteredCmds.length) {
    results.push({ cat: true, name: 'Comandos' });
    filteredCmds.forEach(c => results.push({ ...c, type: 'cmd' }));
  }

  const filteredSessions = sessions.filter(s => !q || s.name.toLowerCase().includes(q));
  if (filteredSessions.length) {
    results.push({ cat: true, name: 'Conversaciones' });
    filteredSessions.slice(0, 8).forEach(s => results.push({
      type: 'session', icon: '💬', title: s.name, hint: s.id.slice(-6), session: s
    }));
  }

  cmdResults = results.filter(r => !r.cat);

  if (!results.length) {
    list.innerHTML = '<div class="cmdpal-empty">No hay resultados</div>';
    return;
  }

  let html = '';
  let realIdx = 0;
  results.forEach(r => {
    if (r.cat) { html += `<div class="cmdpal-cat">${escapeHtml(r.name)}</div>`; return; }
    const isActive = realIdx === cmdActiveIdx;
    html += `<div class="cmdpal-item${isActive ? ' active' : ''}" data-realidx="${realIdx}">
      <span class="icon">${r.icon || '·'}</span>
      <span class="title">${escapeHtml(r.title)}</span>
      ${r.hint ? `<span class="hint">${escapeHtml(r.hint)}</span>` : ''}
    </div>`;
    realIdx++;
  });
  list.innerHTML = html;

  list.querySelectorAll('.cmdpal-item').forEach(el => {
    el.addEventListener('click', () => { runCmdResult(+el.dataset.realidx); });
  });
}

function runCmdResult(idx) {
  const r = cmdResults[idx]; if (!r) return;
  closeCmdPal();
  if (r.type === 'cmd') setTimeout(() => r.run(), 10);
  else if (r.type === 'session') switchSession(r.session.id);
}

$('cmdpalInput').addEventListener('input', e => {
  cmdActiveIdx = 0;
  renderCmdResults(e.target.value);
});
$('cmdpalInput').addEventListener('keydown', e => {
  if (e.key === 'Escape') { closeCmdPal(); e.preventDefault(); return; }
  if (e.key === 'ArrowDown') { e.preventDefault(); cmdActiveIdx = Math.min(cmdActiveIdx + 1, cmdResults.length - 1); renderCmdResults($('cmdpalInput').value); return; }
  if (e.key === 'ArrowUp') { e.preventDefault(); cmdActiveIdx = Math.max(cmdActiveIdx - 1, 0); renderCmdResults($('cmdpalInput').value); return; }
  if (e.key === 'Enter') { e.preventDefault(); runCmdResult(cmdActiveIdx); return; }
});
$('cmdpal').addEventListener('click', e => { if (e.target === $('cmdpal')) closeCmdPal(); });

/* ═══════════ CÁMARA ═══════════ */
let cameraStream = null;
$('cameraButton').addEventListener('click', async () => {
  try {
    cameraStream = await navigator.mediaDevices.getUserMedia({video:true});
    $('cameraVideo').srcObject = cameraStream;
    $('cameraDialog').showModal();
  } catch (err) { toast('No se pudo acceder a la cámara: ' + err.message,'error'); }
});
$('closeCamBtn').addEventListener('click', () => {
  if (cameraStream) { cameraStream.getTracks().forEach(t => t.stop()); cameraStream = null; }
  $('cameraDialog').close();
});
$('snapBtn').addEventListener('click', () => {
  if (!cameraStream) return;
  const c = document.createElement('canvas');
  c.width = $('cameraVideo').videoWidth;
  c.height = $('cameraVideo').videoHeight;
  c.getContext('2d').drawImage($('cameraVideo'), 0, 0);
  const dataUrl = c.toDataURL('image/jpeg', 0.85);
  const s = getCurrentSession(); if (!s) return;
  s.messages.push({role:'user', text:'📷 [Imagen capturada]', image:dataUrl, ts:Date.now()});
  s.updatedAt = Date.now();
  saveData(); renderMessages();
  toast('Foto capturada (backend no procesa imágenes aún)');
  $('closeCamBtn').click();
});

/* ═══════════ ATAJOS ═══════════ */
const DEFAULT_SHORTCUTS = {search:'Shift+K', send:'Shift+Enter', think:'Shift+I', sidebar:'Shift+B', rightSidebar:'Shift+N', cmdk:'Ctrl+K'};
let shortcuts = {...DEFAULT_SHORTCUTS};
try { shortcuts = {...DEFAULT_SHORTCUTS, ...JSON.parse(localStorage.getItem('makka-shortcuts')||'{}')}; } catch {}
function comboFromEvent(e) {
  const mods = [];
  if (e.ctrlKey) mods.push('Ctrl');
  if (e.shiftKey) mods.push('Shift');
  if (e.altKey) mods.push('Alt');
  if (e.metaKey) mods.push('Meta');
  const k = e.key;
  if (['Shift','Control','Alt','Meta'].includes(k)) return null;
  if (!mods.length) return null;
  return [...mods, k.length===1?k.toUpperCase():k].join('+');
}
function formatCombo(c) { return c.split('+').join(' + '); }
function updateComposerHint() {
  const el = $('composerHint');
  if (el) el.textContent = `${formatCombo(shortcuts.send)} enviar · ${formatCombo(shortcuts.cmdk)} comandos · ${formatCombo(shortcuts.rightSidebar)} panel`;
}
function updateShortcutButtons() { document.querySelectorAll('.shortcut-input').forEach(btn => { btn.textContent = formatCombo(shortcuts[btn.dataset.action]); }); }
updateComposerHint(); updateShortcutButtons();

let capturingFor = null;
$('editShortcutsBtn').addEventListener('click', () => { $('settingsDropdown').classList.remove('open'); updateShortcutButtons(); $('shortcutsModal').classList.add('open'); });
$('shortcutsClose').addEventListener('click', () => { $('shortcutsModal').classList.remove('open'); if (capturingFor) { capturingFor.classList.remove('capturing'); capturingFor.textContent = formatCombo(shortcuts[capturingFor.dataset.action]); capturingFor = null; } });
$('shortcutsModal').addEventListener('click', e => { if (e.target === $('shortcutsModal')) $('shortcutsClose').click(); });
$('shortcutsReset').addEventListener('click', () => {
  shortcuts = {...DEFAULT_SHORTCUTS};
  localStorage.setItem('makka-shortcuts', JSON.stringify(shortcuts));
  updateShortcutButtons(); updateComposerHint();
  if (capturingFor) { capturingFor.classList.remove('capturing'); capturingFor = null; }
  toast('Atajos restaurados');
});
document.querySelectorAll('.shortcut-input').forEach(btn => {
  btn.addEventListener('click', () => {
    if (capturingFor === btn) { btn.classList.remove('capturing'); btn.textContent = formatCombo(shortcuts[btn.dataset.action]); capturingFor = null; return; }
    if (capturingFor) { capturingFor.classList.remove('capturing'); capturingFor.textContent = formatCombo(shortcuts[capturingFor.dataset.action]); }
    capturingFor = btn; btn.classList.add('capturing'); btn.textContent = 'Pulsa teclas…';
  });
});

document.addEventListener('keydown', e => {
  if (capturingFor) {
    e.preventDefault(); e.stopPropagation();
    if (e.key === 'Escape') { capturingFor.classList.remove('capturing'); capturingFor.textContent = formatCombo(shortcuts[capturingFor.dataset.action]); capturingFor = null; return; }
    const combo = comboFromEvent(e); if (!combo) return;
    shortcuts[capturingFor.dataset.action] = combo;
    localStorage.setItem('makka-shortcuts', JSON.stringify(shortcuts));
    capturingFor.classList.remove('capturing'); capturingFor.textContent = formatCombo(combo); capturingFor = null;
    updateComposerHint(); return;
  }
  if ($('cmdpal').classList.contains('open')) return;
  if ($('shortcutsModal').classList.contains('open')) { if (e.key === 'Escape') $('shortcutsClose').click(); return; }
  if ($('promptsModal').classList.contains('open')) { if (e.key === 'Escape') $('promptsModal').classList.remove('open'); return; }
  if ($('sysPromptModal').classList.contains('open')) { if (e.key === 'Escape') $('sysPromptModal').classList.remove('open'); return; }
  if ($('metaModal').classList.contains('open')) { if (e.key === 'Escape') $('metaModal').classList.remove('open'); return; }
  if ($('rightPanelModal').classList.contains('open')) { if (e.key === 'Escape') $('rightPanelModal').classList.remove('open'); return; }
  if ($('layoutModal').classList.contains('open')) { if (e.key === 'Escape') $('layoutModal').classList.remove('open'); return; }

  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k' && !e.shiftKey) {
    e.preventDefault(); openCmdPal(); return;
  }

  if (e.key === 'Escape') {
    if (multiSelectMode) { exitMultiSelect(); return; }
    $('settingsDropdown').classList.remove('open');
    closeMoveMenu();
    return;
  }

  const t = e.target;
  const inTextField = t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable);

  const combo = comboFromEvent(e);

  if (combo === shortcuts.send) {
    e.preventDefault();
    if (generating) stopGeneration(); else sendMessage();
    return;
  }

  if (inTextField) return;
  if (!combo) return;

  if (combo === shortcuts.sidebar)      { e.preventDefault(); toggleSidebar(); return; }
  if (combo === shortcuts.rightSidebar) { e.preventDefault(); toggleRightSidebar(); return; }
  if (combo === shortcuts.think)        { e.preventDefault(); $('thinkButton').click(); return; }
  if (combo === shortcuts.search)       { e.preventDefault(); $('chatInput').focus(); return; }
});

/* ═══════════ DICTADO ═══════════ */
let recognition = null;
const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
if (SR) {
  recognition = new SR();
  recognition.lang = 'es-ES'; recognition.continuous = true; recognition.interimResults = true;
  recognition.onresult = ev => {
    let t = '';
    for (let i = ev.resultIndex; i < ev.results.length; i++) t += ev.results[i][0].transcript;
    $('chatInput').value = t;
    autoResizeInput(); updateCharCount(); updateSendButtonState();
  };
  recognition.onend = () => { $('voiceButton').classList.remove('recording'); $('voiceButton').textContent = '🎙'; };
}
$('voiceButton').addEventListener('click', () => {
  if (!recognition) { addLog('WARN','Dictado no compatible.'); return toast('Voz no disponible','error'); }
  if ($('voiceButton').classList.contains('recording')) recognition.stop();
  else {
    try { recognition.start(); $('voiceButton').classList.add('recording'); $('voiceButton').textContent = '■'; }
    catch { addLog('WARN','No se pudo iniciar dictado.'); }
  }
});

/* ═══════════ RESIZE ═══════════ */
window.addEventListener('resize', () => {
  document.querySelectorAll('.section-body:not(.collapsed)').forEach(b => { b.style.maxHeight = b.scrollHeight + 'px'; });
  syncSidebarIcon();
});
document.querySelector('.workspace').addEventListener('click', () => {
  if (window.innerWidth <= 650) $('sidebar').classList.remove('open');
});

/* ═══════════ INIT ═══════════ */
(async () => {
  await loadData();
  applyLeftOrder();
  applyRightOrder();
  applyVisibility();
  renderSessionTree();
  renderMessages();
  updateChatHeading();
  updateSysPromptBanner();
  applyRightSections();
  autoResizeInput();
  updateCharCount();
  updateTokenChart();
  updateContextIndicator();

  checkAIStatus().then(() => {
    checkAgents();
    addLog('INFO', aiRunning ? 'Conectado al backend.' : 'IA apagada. Pulsa ON para arrancar.');
  });
})();

setInterval(() => { if (!document.hidden) checkAIStatus(); }, 6000);
setInterval(() => { if (!document.hidden) checkAgents();   }, 8000);
setInterval(() => { if (!document.hidden) fetchServerLogs(); }, 3500);
fetchServerLogs();

setTimeout(() => $('chatInput').focus(), 100);
</script>
</body>
</html>
