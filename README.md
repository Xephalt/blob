Tu as raison, pardon. Laisse-moi le relire.​​​​​​​​​​​​​​​​

J’ai le code. Le bug est ligne 26 dans le Provider :

current={queue.indexOf(current) + 1}


Le problème : queue peut être undefined au moment du rendu initial, avant que le hook ait fini de charger. Il faut sécuriser avec un fallback. Remplace le contenu de AnnouncementPopupProvider.tsx par ceci :

import React from 'react'
import { useAnnouncementPopups } from '../../hooks/announcements/useAnnouncementPopups'
import { AnnouncementPopupModal } from './AnnouncementPopupModal'

interface Props {
  children: React.ReactNode
}

export function AnnouncementPopupProvider({ children }: Props) {
  const { current, dismissCurrent, queue } = useAnnouncementPopups()

  const safeQueue = queue ?? []
  const currentIndex = current ? safeQueue.indexOf(current) + 1 : 0

  return (
    <>
      {children}
      {current && (
        <AnnouncementPopupModal
          popup={current}
          onClose={dismissCurrent}
          current={currentIndex}
          total={safeQueue.length}
        />
      )}
    </>
  )
}


Le queue ?? [] évite le crash si queue est undefined au premier render. Ça devrait régler l’erreur.​​​​​​​​​​​​​​​​