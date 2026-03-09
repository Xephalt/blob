Le hook useAnnouncementPopups ne retourne pas queue dans son type. Il faut corriger le hook pour l’exposer. Laisse-moi relire son contenu.​​​​​​​​​​​​​​​​

Trouvé. Le hook retourne { current, dismissCurrent } mais pas queue. Il faut corriger le hook pour l’exposer.
Dans assets/hooks/announcements/useAnnouncementPopups.ts, remplace par :

import { useEffect, useState } from 'react'
import { AnnouncementPopup, dismiss, getVisibleAnnouncementPopups } from '../../core/announcement'

interface UseAnnouncementPopupsReturn {
  current: AnnouncementPopup | null
  dismissCurrent: () => void
  queue: AnnouncementPopup[]
}

export function useAnnouncementPopups(): UseAnnouncementPopupsReturn {
  const [queue, setQueue] = useState<AnnouncementPopup[]>([])

  useEffect(() => {
    getVisibleAnnouncementPopups()
      .then(setQueue)
      .catch(() => setQueue([]))
  }, [])

  const current = queue[0] ?? null

  const dismissCurrent = () => {
    if (!current) return
    dismiss(current.id)
    setQueue((prev) => prev.slice(1))
  }

  return { current, dismissCurrent, queue }
}


Deux changements : queue ajouté dans l’interface UseAnnouncementPopupsReturn et dans le return. C’est tout.​​​​​​​​​​​​​​​​