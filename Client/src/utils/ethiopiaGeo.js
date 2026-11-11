export const DESSIE_CENTER = { lat: 11.131, lng: 39.638, elev: 2470 };

export const KNOWN_GEO_COORDINATES = {
  dessie: { lat: 11.1333, lng: 39.6333, elev: 2470 },
  piazza_dessie: { lat: 11.1198, lng: 39.6342, elev: 2470 },
  hotte_dessie: { lat: 11.1255, lng: 39.6352, elev: 2485 },
  menafesha_dessie: { lat: 11.1305, lng: 39.641, elev: 2475 },
  buanbua_dessie: { lat: 11.1382, lng: 39.6562, elev: 2510 },
  erobit_dessie: { lat: 11.1342, lng: 39.6358, elev: 2490 },
  segno_dessie: { lat: 11.122, lng: 39.6448, elev: 2460 },
  wollo_dessie: { lat: 11.149, lng: 39.6495, elev: 2530 },
  kurkur_dessie: { lat: 11.1145, lng: 39.6275, elev: 2480 },
  hospital_dessie: { lat: 11.1282, lng: 39.6322, elev: 2500 },
  kombolcha: { lat: 11.0833, lng: 39.7333, elev: 1857 },
  woldiya: { lat: 11.8333, lng: 39.6, elev: 2112 },
  addis_ababa: { lat: 9.0108, lng: 38.7495, elev: 2355 },
  bahir_dar: { lat: 11.5936, lng: 37.3908, elev: 1800 },
  gondar: { lat: 12.603, lng: 37.4521, elev: 2133 },
  hawassa: { lat: 7.0504, lng: 38.4955, elev: 1708 },
  adama: { lat: 8.54, lng: 39.27, elev: 1712 },
  mekelle: { lat: 13.4967, lng: 39.4753, elev: 2084 },
  dire_dawa: { lat: 9.6009, lng: 41.8501, elev: 1276 },
  debre_birhan: { lat: 9.68, lng: 39.53, elev: 2840 },
  jimma: { lat: 7.6734, lng: 36.8344, elev: 1780 },
  harar: { lat: 9.3149, lng: 42.1286, elev: 1885 },
};

/**
 * Resolve real latitude & longitude for a branch:
 * 1) Explicit database latitude/longitude when set.
 * 2) Intelligent Dessie & Ethiopian locality matching against branch name, address, city & region.
 */
export function resolveBranchCoordinates(branch) {
  if (!branch) return { ...DESSIE_CENTER, isExactDbCoords: false };

  const lat = Number(branch.latitude);
  const lng = Number(branch.longitude);
  if (
    branch.latitude !== null &&
    branch.latitude !== undefined &&
    branch.longitude !== null &&
    branch.longitude !== undefined &&
    branch.latitude !== '' &&
    branch.longitude !== '' &&
    Number.isFinite(lat) &&
    Number.isFinite(lng) &&
    (Math.abs(lat) > 0.001 || Math.abs(lng) > 0.001)
  ) {
    const cityLower = (branch.city || '').toLowerCase();
    return {
      lat,
      lng,
      elev: cityLower.includes('kombol') ? 1857 : 2470,
      isExactDbCoords: true,
    };
  }

  const fullText = `${branch.branch_name || ''} ${branch.address || ''} ${branch.city || ''} ${branch.region || ''}`.toLowerCase();

  // Dessie neighborhoods (checked before city fallback; supports spelling variations)
  if (fullText.includes('buanbu') || fullText.includes('buabu') || fullText.includes('buanbh') || fullText.includes('ቧንቧ')) {
    return { ...KNOWN_GEO_COORDINATES.buanbua_dessie, isExactDbCoords: false };
  }
  if (fullText.includes('erobit') || fullText.includes('robit') || fullText.includes('ሮቢት')) {
    return { ...KNOWN_GEO_COORDINATES.erobit_dessie, isExactDbCoords: false };
  }
  if (fullText.includes('hotte') || fullText.includes('hote') || fullText.includes('akale') || fullText.includes('ሆቴ')) {
    return { ...KNOWN_GEO_COORDINATES.hotte_dessie, isExactDbCoords: false };
  }
  if (fullText.includes('menafesha') || fullText.includes('መናፈሻ')) {
    return { ...KNOWN_GEO_COORDINATES.menafesha_dessie, isExactDbCoords: false };
  }
  if (fullText.includes('segno') || fullText.includes('ሰኞ') || fullText.includes('shell')) {
    return { ...KNOWN_GEO_COORDINATES.segno_dessie, isExactDbCoords: false };
  }
  if (fullText.includes('wollo') || fullText.includes('tita') || fullText.includes('ወሎ')) {
    return { ...KNOWN_GEO_COORDINATES.wollo_dessie, isExactDbCoords: false };
  }
  if (fullText.includes('kurkur') || fullText.includes('ኩርኩር')) {
    return { ...KNOWN_GEO_COORDINATES.kurkur_dessie, isExactDbCoords: false };
  }
  if (fullText.includes('hospital') || fullText.includes('ሆስፒታል')) {
    return { ...KNOWN_GEO_COORDINATES.hospital_dessie, isExactDbCoords: false };
  }
  if (fullText.includes('piazza') || fullText.includes('piassa') || fullText.includes('arada') || fullText.includes('ፒያሳ') || fullText.includes('አራዳ')) {
    if (fullText.includes('des') || fullText.includes('ደሴ') || !fullText.includes('addis')) {
      return { ...KNOWN_GEO_COORDINATES.piazza_dessie, isExactDbCoords: false };
    }
  }

  if (fullText.includes('kombol') || fullText.includes('ኮምቦል')) {
    return { ...KNOWN_GEO_COORDINATES.kombolcha, isExactDbCoords: false };
  }
  if (fullText.includes('wold') || fullText.includes('ወልድ')) {
    return { ...KNOWN_GEO_COORDINATES.woldiya, isExactDbCoords: false };
  }
  if (fullText.includes('des') || fullText.includes('ደሴ') || fullText.includes('amhara') || fullText.includes('አማራ')) {
    return { ...KNOWN_GEO_COORDINATES.dessie, isExactDbCoords: false };
  }
  if (fullText.includes('bahir') || fullText.includes('ባሕር')) {
    return { ...KNOWN_GEO_COORDINATES.bahir_dar, isExactDbCoords: false };
  }
  if (fullText.includes('gond') || fullText.includes('ጎንደር')) {
    return { ...KNOWN_GEO_COORDINATES.gondar, isExactDbCoords: false };
  }
  if (fullText.includes('hawas') || fullText.includes('ሐዋሳ')) {
    return { ...KNOWN_GEO_COORDINATES.hawassa, isExactDbCoords: false };
  }
  if (fullText.includes('adama') || fullText.includes('አዳማ') || fullText.includes('nazret')) {
    return { ...KNOWN_GEO_COORDINATES.adama, isExactDbCoords: false };
  }
  if (fullText.includes('mekel') || fullText.includes('መቀሌ')) {
    return { ...KNOWN_GEO_COORDINATES.mekelle, isExactDbCoords: false };
  }
  if (fullText.includes('dire') || fullText.includes('ድሬ')) {
    return { ...KNOWN_GEO_COORDINATES.dire_dawa, isExactDbCoords: false };
  }
  if (fullText.includes('debre') || fullText.includes('ደብረ')) {
    return { ...KNOWN_GEO_COORDINATES.debre_birhan, isExactDbCoords: false };
  }
  if (fullText.includes('addis') || fullText.includes('አዲስ')) {
    return { ...KNOWN_GEO_COORDINATES.addis_ababa, isExactDbCoords: false };
  }

  return { ...KNOWN_GEO_COORDINATES.dessie, isExactDbCoords: false };
}

/**
 * Resolve all branches to map-ready coordinates, ensuring fallback pins at the
 * same fallback center stay visually distinct instead of stacking invisibly.
 */
export function resolveBranchesForMap(branches = []) {
  const seenCoords = new Map();

  return branches.map((b, idx) => {
    const resolved = resolveBranchCoordinates(b);
    let { lat, lng } = resolved;
    const key = `${lat.toFixed(4)},${lng.toFixed(4)}`;
    const count = seenCoords.get(key) || 0;
    seenCoords.set(key, count + 1);

    if (count > 0 && !resolved.isExactDbCoords) {
      const angle = (count * 2.39996); // golden angle spread (~180m steps)
      const radius = 0.0022 * Math.ceil(count / 2);
      lat = Number((lat + Math.cos(angle) * radius).toFixed(6));
      lng = Number((lng + Math.sin(angle) * radius).toFixed(6));
    }

    return {
      ...b,
      lat,
      lng,
      elev: resolved.elev,
      isExactDbCoords: resolved.isExactDbCoords,
      mapIndex: idx,
    };
  });
}

/**
 * Cleanly format branch address with real city and region without duplication.
 */
export function formatBranchLocation(branch, isAmharic = false) {
  if (!branch) return isAmharic ? 'ደሴ፣ አማራ ክልል' : 'Dessie, Amhara Region';

  const parts = [];
  const addr = (branch.address || branch.address_line1 || '').trim();
  const city = (branch.city || '').trim() || (isAmharic ? 'ደሴ' : 'Dessie');
  const region = (branch.region || '').trim() || (isAmharic ? 'አማራ ክልል' : 'Amhara Region');

  if (addr) parts.push(addr);
  if (city && !addr.toLowerCase().includes(city.toLowerCase())) {
    parts.push(city);
  }
  if (region && !parts.join(' ').toLowerCase().includes(region.toLowerCase())) {
    parts.push(region);
  }

  return parts.join(', ');
}

