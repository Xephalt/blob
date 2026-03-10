Je vois le résultat — le popup fonctionne ! Voici les fichiers mis à jour avec le nouveau design.
AnnouncementPopupModal.tsx :

import React from 'react'
import BasicModal from './BasicModal'
import { AnnouncementPopup } from '../../core/announcement'

interface Props {
  popup: AnnouncementPopup
  onClose: () => void
  current: number
  total: number
  onNext: () => void
  onPrev: () => void
  onFinish: () => void
  viewed: number[]
}

export function AnnouncementPopupModal({ popup, onClose, current, total, onNext, onPrev, onFinish, viewed }: Props) {
  const isFirst = current === 1
  const isLast = current === total

  return (
    <BasicModal open onClose={onClose} removeButtons={true} setOpen={onFinish}>
      <div className="announcement-popup">

        {/* Header */}
        <div className="announcement-popup__header" style={{
          borderBottom: '1px solid #e5e7eb',
          paddingBottom: '12px',
          marginBottom: '16px',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center'
        }}>
          <h2 style={{ margin: 0, fontSize: '1.25rem', fontWeight: 700, color: '#1a1a1a' }}>
            Astuces et nouveautés CommsGPT !
          </h2>
        </div>

        {/* Body 60/40 */}
        <div style={{ display: 'flex', gap: '24px', minHeight: '280px' }}>

          {/* Gauche 60% - image + titre */}
          <div style={{ flex: '0 0 60%' }}>
            <h3 style={{ fontSize: '1rem', fontWeight: 600, marginBottom: '12px', color: '#1a1a1a' }}>
              {current} - {popup.title}
            </h3>
            {popup.imageUrl && (
              <img
                src={popup.imageUrl}
                alt=""
                style={{ width: '100%', borderRadius: '8px', border: '2px solid #e5e7eb' }}
              />
            )}
          </div>

          {/* Droite 40% - description */}
          <div style={{
            flex: '0 0 40%',
            backgroundColor: '#f9fafb',
            borderRadius: '8px',
            padding: '20px',
            display: 'flex',
            flexDirection: 'column',
            justifyContent: 'center'
          }}>
            <h4 style={{ fontWeight: 700, marginBottom: '10px', color: '#1a1a1a' }}>Description</h4>
            <div
              style={{ color: '#4b5563', fontSize: '0.9rem', lineHeight: '1.6' }}
              dangerouslySetInnerHTML={{ __html: popup.content }}
            />
          </div>
        </div>

        {/* Footer : prev | dots | next */}
        <div style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          marginTop: '24px',
          paddingTop: '16px',
          borderTop: '1px solid #e5e7eb'
        }}>

          {/* Bouton précédent */}
          <button
            onClick={onPrev}
            disabled={isFirst}
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: '6px',
              padding: '8px 16px',
              border: 'none',
              borderRadius: '6px',
              backgroundColor: isFirst ? '#f3f4f6' : '#e5e7eb',
              color: isFirst ? '#9ca3af' : '#374151',
              cursor: isFirst ? 'not-allowed' : 'pointer',
              fontWeight: 500,
              fontSize: '0.875rem'
            }}
          >
            <span className="material-icons" style={{ fontSize: '18px' }}>chevron_left</span>
            Précédent
          </button>

          {/* Dots */}
          <div style={{ display: 'flex', gap: '8px', alignItems: 'center' }}>
            {Array(total).fill(0).map((_, index) => {
              const dotNum = index + 1
              const isDone = viewed.includes(index) || dotNum < current
              const isCurrent = dotNum === current
              return (
                <span
                  key={index}
                  style={{
                    width: isCurrent ? '10px' : '8px',
                    height: isCurrent ? '10px' : '8px',
                    borderRadius: '50%',
                    backgroundColor: isCurrent ? '#00a862' : isDone ? '#86efac' : '#d1d5db',
                    transition: 'all 0.2s',
                    display: 'inline-block'
                  }}
                />
              )
            })}
          </div>

          {/* Bouton suivant / terminer */}
          {isLast ? (
            <button
              onClick={onFinish}
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '6px',
                padding: '8px 16px',
                border: 'none',
                borderRadius: '6px',
                backgroundColor: '#00a862',
                color: 'white',
                cursor: 'pointer',
                fontWeight: 500,
                fontSize: '0.875rem'
              }}
            >
              Terminer
            </button>
          ) : (
            <button
              onClick={onNext}
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '6px',
                padding: '8px 16px',
                border: 'none',
                borderRadius: '6px',
                backgroundColor: '#00a862',
                color: 'white',
                cursor: 'pointer',
                fontWeight: 500,
                fontSize: '0.875rem'
              }}
            >
              Astuce suivante ({current}/{total})
              <span className="material-icons" style={{ fontSize: '18px' }}>chevron_right</span>
            </button>
          )}
        </div>

      </div>
    </BasicModal>
  )
}


Et voici l’artefact visuel pour visualiser le rendu :

import { useState } from "react"

const mockPopups = [
  { id: '1', title: 'Modifier un précédent message', content: 'Vous pouvez modifier vos messages, cela est utile si vous voulez retravailler un message précédent sans avoir à le réécrire complètement.', imageUrl: null },
  { id: '2', title: 'Utiliser les favoris', content: 'Sauvegardez vos requêtes favorites pour y accéder rapidement depuis la barre latérale.', imageUrl: null },
  { id: '3', title: 'Changer la langue', content: 'Basculez facilement entre le français et l\'anglais grâce au sélecteur en haut à droite du popup.', imageUrl: null },
]

export default function AnnouncementPopupPreview() {
  const [currentIndex, setCurrentIndex] = useState(0)
  const [viewed, setViewed] = useState([])
  const [closed, setClosed] = useState(false)

  const total = mockPopups.length
  const current = currentIndex + 1
  const popup = mockPopups[currentIndex]
  const isFirst = current === 1
  const isLast = current === total

  const onNext = () => {
    if (currentIndex < total - 1) {
      setViewed(prev => [...prev, currentIndex])
      setCurrentIndex(prev => prev + 1)
    }
  }
  const onPrev = () => {
    if (currentIndex > 0) setCurrentIndex(prev => prev - 1)
  }
  const onFinish = () => setClosed(true)

  if (closed) return (
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', height: '100vh', fontFamily: 'sans-serif' }}>
      <button onClick={() => { setClosed(false); setCurrentIndex(0); setViewed([]) }}
        style={{ padding: '10px 20px', backgroundColor: '#00a862', color: 'white', border: 'none', borderRadius: '8px', cursor: 'pointer' }}>
        Rouvrir le popup
      </button>
    </div>
  )

  return (
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', height: '100vh', backgroundColor: 'rgba(0,0,0,0.4)', fontFamily: 'sans-serif' }}>
      <div style={{ backgroundColor: 'white', borderRadius: '12px', padding: '28px', width: '720px', maxWidth: '95vw', boxShadow: '0 20px 60px rgba(0,0,0,0.3)', position: 'relative' }}>

        {/* Croix fermer */}
        <button onClick={onFinish} style={{ position: 'absolute', top: '16px', right: '16px', background: 'none', border: 'none', fontSize: '20px', cursor: 'pointer', color: '#6b7280', lineHeight: 1 }}>✕</button>

        {/* Header */}
        <div style={{ borderBottom: '1px solid #e5e7eb', paddingBottom: '12px', marginBottom: '20px' }}>
          <h2 style={{ margin: 0, fontSize: '1.2rem', fontWeight: 700, color: '#1a1a1a' }}>Astuces et nouveautés CommsGPT !</h2>
        </div>

        {/* Body 60/40 */}
        <div style={{ display: 'flex', gap: '20px', minHeight: '220px' }}>
          <div style={{ flex: '0 0 58%' }}>
            <h3 style={{ fontSize: '0.95rem', fontWeight: 600, marginBottom: '12px', color: '#1a1a1a' }}>
              {current} - {popup.title}
            </h3>
            <div style={{ width: '100%', height: '180px', backgroundColor: '#f3f4f6', borderRadius: '8px', border: '2px solid #e5e7eb', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#9ca3af', fontSize: '0.85rem' }}>
              [ Capture d'écran ]
            </div>
          </div>
          <div style={{ flex: '0 0 38%', backgroundColor: '#f9fafb', borderRadius: '8px', padding: '18px', display: 'flex', flexDirection: 'column', justifyContent: 'center' }}>
            <h4 style={{ fontWeight: 700, marginBottom: '10px', color: '#1a1a1a', margin: '0 0 10px 0' }}>Description</h4>
            <p style={{ color: '#4b5563', fontSize: '0.875rem', lineHeight: '1.6', margin: 0 }}>{popup.content}</p>
          </div>
        </div>

        {/* Footer */}
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: '20px', paddingTop: '16px', borderTop: '1px solid #e5e7eb' }}>

          <button onClick={onPrev} disabled={isFirst} style={{ display: 'flex', alignItems: 'center', gap: '4px', padding: '8px 14px', border: 'none', borderRadius: '6px', backgroundColor: isFirst ? '#f3f4f6' : '#e5e7eb', color: isFirst ? '#9ca3af' : '#374151', cursor: isFirst ? 'not-allowed' : 'pointer', fontWeight: 500, fontSize: '0.85rem' }}>
            ← Précédent
          </button>

          <div style={{ display: 'flex', gap: '8px', alignItems: 'center' }}>
            {Array(total).fill(0).map((_, index) => {
              const dotNum = index + 1
              const isDone = viewed.includes(index) || dotNum < current
              const isCurrent = dotNum === current
              return (
                <span key={index} style={{ width: isCurrent ? '10px' : '8px', height: isCurrent ? '10px' : '8px', borderRadius: '50%', backgroundColor: isCurrent ? '#00a862' : isDone ? '#86efac' : '#d1d5db', transition: 'all 0.2s', display: 'inline-block' }} />
              )
            })}
          </div>

          {isLast ? (
            <button onClick={onFinish} style={{ padding: '8px 16px', border: 'none', borderRadius: '6px', backgroundColor: '#00a862', color: 'white', cursor: 'pointer', fontWeight: 500, fontSize: '0.85rem' }}>
              Terminer
            </button>
          ) : (
            <button onClick={onNext} style={{ display: 'flex', alignItems: 'center', gap: '4px', padding: '8px 14px', border: 'none', borderRadius: '6px', backgroundColor: '#00a862', color: 'white', cursor: 'pointer', fontWeight: 500, fontSize: '0.85rem' }}>
              Astuce suivante ({current}/{total}) →
            </button>
          )}
        </div>

      </div>
    </div>
  )
}


Les changements clés : dots verts/gris centrés entre les deux boutons, flèches ← Précédent et Astuce suivante →, bouton vert BNPP pour l’action principale, layout 60/40 conservé avec la description en fond gris clair.​​​​​​​​​​​​​​​​