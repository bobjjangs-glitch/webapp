// data/products.js
// 타이어픽 크롤링 기반 실제 제품 데이터

const TT_PRODUCTS = {

  // =============================================
  // 🔵 타이어 (Tire)
  // =============================================
  tire: [
    // ── 금호 ──────────────────────────────────
    {
      id: "tire-001",
      brand: "금호",
      brandKey: "kumho",
      name: "솔루스 TA21",
      size: "245/45R18",
      price: 95060,
      originalPrice: 198000,
      discount: 52,
      rating: 4.8,
      reviewCount: 199,
      badge: null,
      tags: ["승용차", "사계절용", "고급형"],
      category: "tire",
      image: "https://images.kumhousa.com/products/solus-ta21.png",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=금호+솔루스+TA21",
      season: "사계절",
      vehicleType: "승용차",
      grade: "고급형"
    },
    {
      id: "tire-002",
      brand: "금호",
      brandKey: "kumho",
      name: "솔루스 TA21",
      size: "225/55R17",
      price: 88270,
      originalPrice: 166500,
      discount: 47,
      rating: 4.8,
      reviewCount: 199,
      badge: null,
      tags: ["승용차", "사계절용", "고급형"],
      category: "tire",
      image: "https://images.kumhousa.com/products/solus-ta21.png",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=금호+솔루스+TA21",
      season: "사계절",
      vehicleType: "승용차",
      grade: "고급형"
    },
    {
      id: "tire-003",
      brand: "금호",
      brandKey: "kumho",
      name: "솔루스 TA21",
      size: "215/55R17",
      price: 71780,
      originalPrice: 149500,
      discount: 52,
      rating: 4.8,
      reviewCount: 199,
      badge: null,
      tags: ["승용차", "사계절용", "고급형"],
      category: "tire",
      image: "https://images.kumhousa.com/products/solus-ta21.png",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=금호+솔루스+TA21",
      season: "사계절",
      vehicleType: "승용차",
      grade: "고급형"
    },
    {
      id: "tire-004",
      brand: "금호",
      brandKey: "kumho",
      name: "솔루스 TA21",
      size: "225/45R17",
      price: 89240,
      originalPrice: 168300,
      discount: 47,
      rating: 4.8,
      reviewCount: 199,
      badge: null,
      tags: ["승용차", "사계절용", "고급형"],
      category: "tire",
      image: "https://images.kumhousa.com/products/solus-ta21.png",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=금호+솔루스+TA21",
      season: "사계절",
      vehicleType: "승용차",
      grade: "고급형"
    },
    {
      id: "tire-005",
      brand: "금호",
      brandKey: "kumho",
      name: "솔루스 TA21",
      size: "205/60R16",
      price: 82450,
      originalPrice: 164900,
      discount: 50,
      rating: 4.8,
      reviewCount: 199,
      badge: "BEST",
      tags: ["승용차", "사계절용", "고급형"],
      category: "tire",
      image: "https://images.kumhousa.com/products/solus-ta21.png",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=금호+솔루스+TA21",
      season: "사계절",
      vehicleType: "승용차",
      grade: "고급형"
    },
    {
      id: "tire-006",
      brand: "금호",
      brandKey: "kumho",
      name: "솔루스 TA21",
      size: "165/60R14",
      price: 44620,
      originalPrice: 92900,
      discount: 52,
      rating: 4.8,
      reviewCount: 199,
      badge: null,
      tags: ["승용차", "사계절용", "고급형"],
      category: "tire",
      image: "https://images.kumhousa.com/products/solus-ta21.png",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=금호+솔루스+TA21",
      season: "사계절",
      vehicleType: "승용차",
      grade: "고급형"
    },
    {
      id: "tire-007",
      brand: "금호",
      brandKey: "kumho",
      name: "솔루스 TA21",
      size: "175/50R15",
      price: 55290,
      originalPrice: 104300,
      discount: 47,
      rating: 4.8,
      reviewCount: 199,
      badge: null,
      tags: ["승용차", "사계절용", "고급형"],
      category: "tire",
      image: "https://images.kumhousa.com/products/solus-ta21.png",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=금호+솔루스+TA21",
      season: "사계절",
      vehicleType: "승용차",
      grade: "고급형"
    },
    {
      id: "tire-008",
      brand: "금호",
      brandKey: "kumho",
      name: "솔루스 TA21",
      size: "205/55R16",
      price: 62080,
      originalPrice: 124100,
      discount: 50,
      rating: 4.8,
      reviewCount: 199,
      badge: null,
      tags: ["승용차", "사계절용", "고급형"],
      category: "tire",
      image: "https://images.kumhousa.com/products/solus-ta21.png",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=금호+솔루스+TA21",
      season: "사계절",
      vehicleType: "승용차",
      grade: "고급형"
    },
    {
      id: "tire-009",
      brand: "금호",
      brandKey: "kumho",
      name: "크루젠 KL33",
      size: "235/55R19",
      price: 116400,
      originalPrice: 215500,
      discount: 46,
      rating: 4.7,
      reviewCount: 1691,
      badge: null,
      tags: ["사계절용", "SUV", "가성비"],
      category: "tire",
      image: "https://images.kumhousa.com/products/crugen-kl33.png",
      fallbackImage: "https://via.placeholder.com/300x200/117a65/ffffff?text=금호+크루젠+KL33",
      season: "사계절",
      vehicleType: "SUV",
      grade: "가성비"
    },
    {
      id: "tire-010",
      brand: "금호",
      brandKey: "kumho",
      name: "크루젠 HP71",
      size: "225/55R18",
      price: 119310,
      originalPrice: 198850,
      discount: 40,
      rating: 4.7,
      reviewCount: 3088,
      badge: "BEST",
      tags: ["사계절용", "SUV", "고급형", "품절임박 4개"],
      category: "tire",
      image: "https://images.kumhousa.com/products/crugen-hp71.png",
      fallbackImage: "https://via.placeholder.com/300x200/117a65/ffffff?text=금호+크루젠+HP71",
      season: "사계절",
      vehicleType: "SUV",
      grade: "고급형"
    },
    {
      id: "tire-011",
      brand: "금호",
      brandKey: "kumho",
      name: "크루젠 KL33",
      size: "235/60R18",
      price: 114460,
      originalPrice: 224400,
      discount: 49,
      rating: 4.7,
      reviewCount: 1691,
      badge: null,
      tags: ["사계절용", "SUV", "가성비"],
      category: "tire",
      image: "https://images.kumhousa.com/products/crugen-kl33.png",
      fallbackImage: "https://via.placeholder.com/300x200/117a65/ffffff?text=금호+크루젠+KL33",
      season: "사계절",
      vehicleType: "SUV",
      grade: "가성비"
    },
    {
      id: "tire-012",
      brand: "금호",
      brandKey: "kumho",
      name: "마제스티9 솔루스 TA91",
      size: "215/55R17",
      price: 107670,
      originalPrice: 203100,
      discount: 47,
      rating: 4.8,
      reviewCount: 2555,
      badge: "HOT",
      tags: ["사계절용", "승용차", "고급형"],
      category: "tire",
      image: "https://images.kumhousa.com/products/majesty9-ta91.png",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=금호+마제스티9+TA91",
      season: "사계절",
      vehicleType: "승용차",
      grade: "고급형"
    },
    {
      id: "tire-013",
      brand: "금호",
      brandKey: "kumho",
      name: "마제스티9 솔루스 TA91",
      size: "225/45R18",
      price: 127070,
      originalPrice: 235300,
      discount: 46,
      rating: 4.8,
      reviewCount: 2555,
      badge: "HOT",
      tags: ["사계절용", "승용차", "고급형"],
      category: "tire",
      image: "https://images.kumhousa.com/products/majesty9-ta91.png",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=금호+마제스티9+TA91",
      season: "사계절",
      vehicleType: "승용차",
      grade: "고급형"
    },
    {
      id: "tire-014",
      brand: "금호",
      brandKey: "kumho",
      name: "윈터크래프트 WP72",
      size: "245/40R19",
      price: 181390,
      originalPrice: 287900,
      discount: 37,
      rating: 4.9,
      reviewCount: 14,
      badge: null,
      tags: ["승용차", "겨울용", "고급형"],
      category: "tire",
      image: "https://images.kumhousa.com/products/wintercraft-wp72.png",
      fallbackImage: "https://via.placeholder.com/300x200/1a5c78/ffffff?text=금호+윈터크래프트+WP72",
      season: "겨울용",
      vehicleType: "승용차",
      grade: "고급형"
    },

    // ── 한국타이어 ────────────────────────────
    {
      id: "tire-015",
      brand: "한국타이어",
      brandKey: "hankook",
      name: "다이나프로 HPX RA43",
      size: "235/60R18",
      price: 149380,
      originalPrice: 253100,
      discount: 41,
      rating: 4.9,
      reviewCount: 232,
      badge: "BEST",
      tags: ["고급형", "사계절용", "SUV"],
      category: "tire",
      image: "https://www.hankooktire.com/content/dam/hankooktire/kr/product/dynapro-hpx.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=한국타이어+다이나프로+HPX",
      season: "사계절",
      vehicleType: "SUV",
      grade: "고급형"
    },
    {
      id: "tire-016",
      brand: "한국타이어",
      brandKey: "hankook",
      name: "벤투스 S2 올시즌",
      size: "245/45R18",
      price: 150350,
      originalPrice: 250580,
      discount: 40,
      rating: 4.8,
      reviewCount: 783,
      badge: null,
      tags: ["승용차", "사계절용", "최고급형"],
      category: "tire",
      image: "https://www.hankooktire.com/content/dam/hankooktire/kr/product/ventus-s2-as.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=한국타이어+벤투스+S2",
      season: "사계절",
      vehicleType: "승용차",
      grade: "최고급형"
    },
    {
      id: "tire-017",
      brand: "한국타이어",
      brandKey: "hankook",
      name: "벤투스 S2 올시즌",
      size: "235/45R18",
      price: 156170,
      originalPrice: 226300,
      discount: 31,
      rating: 4.8,
      reviewCount: 783,
      badge: null,
      tags: ["승용차", "사계절용", "최고급형"],
      category: "tire",
      image: "https://www.hankooktire.com/content/dam/hankooktire/kr/product/ventus-s2-as.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=한국타이어+벤투스+S2",
      season: "사계절",
      vehicleType: "승용차",
      grade: "최고급형"
    },
    {
      id: "tire-018",
      brand: "한국타이어",
      brandKey: "hankook",
      name: "벤투스 S2 올시즌",
      size: "225/45R17",
      price: 140650,
      originalPrice: 195350,
      discount: 28,
      rating: 4.8,
      reviewCount: 783,
      badge: null,
      tags: ["승용차", "사계절용", "최고급형"],
      category: "tire",
      image: "https://www.hankooktire.com/content/dam/hankooktire/kr/product/ventus-s2-as.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=한국타이어+벤투스+S2",
      season: "사계절",
      vehicleType: "승용차",
      grade: "최고급형"
    },

    // ── 굿이어 ────────────────────────────────
    {
      id: "tire-019",
      brand: "굿이어",
      brandKey: "goodyear",
      name: "쿠퍼 제온 RS3-G1",
      size: "245/45R18",
      price: 122400,
      originalPrice: 249800,
      discount: 51,
      rating: 4.8,
      reviewCount: 54,
      badge: null,
      tags: ["고급형", "승용차", "사계절용"],
      category: "tire",
      image: "https://www.goodyear.co.kr/api/products/images/cooper-zeon-rs3-g1.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/1d8348/ffffff?text=굿이어+쿠퍼+제온+RS3",
      season: "사계절",
      vehicleType: "승용차",
      grade: "고급형",
      installment: "최대 12개월 무이자 할부"
    },
    {
      id: "tire-020",
      brand: "굿이어",
      brandKey: "goodyear",
      name: "쿠퍼 디스커버러 HTT",
      size: "235/55R19",
      price: 128350,
      originalPrice: 261900,
      discount: 51,
      rating: 4.9,
      reviewCount: 13,
      badge: null,
      tags: ["사계절용", "승용차/SUV", "고급형"],
      category: "tire",
      image: "https://www.goodyear.co.kr/api/products/images/cooper-discoverer-htt.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/1d8348/ffffff?text=굿이어+쿠퍼+디스커버러",
      season: "사계절",
      vehicleType: "SUV",
      grade: "고급형",
      installment: "최대 12개월 무이자 할부"
    },

    // ── 브리지스톤 ────────────────────────────
    {
      id: "tire-021",
      brand: "브리지스톤",
      brandKey: "bridgestone",
      name: "투란자 세레니티 플러스",
      size: "235/45R18",
      price: 125120,
      originalPrice: 271900,
      discount: 54,
      rating: 4.7,
      reviewCount: 1064,
      badge: null,
      tags: ["사계절용", "승용차", "가성비"],
      category: "tire",
      image: "https://www.bridgestone.co.kr/content/dam/bridgestone/kr/products/turanza-serenity-plus.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/784212/ffffff?text=브리지스톤+투란자",
      season: "사계절",
      vehicleType: "승용차",
      grade: "가성비"
    },
    {
      id: "tire-022",
      brand: "브리지스톤",
      brandKey: "bridgestone",
      name: "투란자 세레니티 플러스",
      size: "245/45R18",
      price: 146700,
      originalPrice: 312100,
      discount: 53,
      rating: 4.7,
      reviewCount: 1064,
      badge: null,
      tags: ["사계절용", "승용차", "가성비"],
      category: "tire",
      image: "https://www.bridgestone.co.kr/content/dam/bridgestone/kr/products/turanza-serenity-plus.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/784212/ffffff?text=브리지스톤+투란자",
      season: "사계절",
      vehicleType: "승용차",
      grade: "가성비"
    },

    // ── 콘티넨탈 ──────────────────────────────
    {
      id: "tire-023",
      brand: "콘티넨탈",
      brandKey: "continental",
      name: "ContiCrossContact LX Sport",
      size: "235/60R18",
      price: 205640,
      originalPrice: 331600,
      discount: 38,
      rating: 4.8,
      reviewCount: 95,
      badge: null,
      tags: ["사계절용", "SUV"],
      category: "tire",
      image: "https://www.continental-tires.com/car/tires/crosscontact-lx-sport.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/2c3e50/ffffff?text=콘티넨탈+LX+Sport",
      season: "사계절",
      vehicleType: "SUV",
      grade: "최고급형"
    },

    // ── 넥센 ──────────────────────────────────
    {
      id: "tire-024",
      brand: "넥센",
      brandKey: "nexen",
      name: "윈가드 스포츠2 WG-Sport2",
      size: "245/45R19",
      price: 171690,
      originalPrice: 260100,
      discount: 34,
      rating: 5.0,
      reviewCount: 5,
      badge: null,
      tags: ["승용차", "겨울용", "고급형"],
      category: "tire",
      image: "https://www.nexentire.com/product/images/winguard-sport2.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=넥센+윈가드+스포츠2",
      season: "겨울용",
      vehicleType: "승용차",
      grade: "고급형"
    },
  ],

  // =============================================
  // 🟠 엔진오일 (Engine Oil)
  // =============================================
  engineoil: [
    // ── 캐스트롤 ──────────────────────────────
    {
      id: "oil-001",
      brand: "캐스트롤",
      brandKey: "castrol",
      name: "마그네틱 HYBRID 0W-20 + 순정 필터 세트",
      viscosity: "0W-20",
      grade: "API SP",
      fuelType: "가솔린/LPG",
      price: 54320,
      originalPrice: 61700,
      discount: 12,
      rating: 4.9,
      reviewCount: 88,
      badge: null,
      tags: ["0W-20", "API SP", "가솔린/LPG"],
      category: "engineoil",
      image: "https://www.castrol.com/content/dam/castrol/products/magnatec-hybrid-0w-20.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=캐스트롤+HYBRID+0W-20"
    },
    {
      id: "oil-002",
      brand: "캐스트롤",
      brandKey: "castrol",
      name: "마그네틱 SN/C3 5W-30 + 순정 필터 세트",
      viscosity: "5W-30",
      grade: "API SN,ACEA C3",
      fuelType: "가솔린/LPG/디젤",
      price: 46851,
      originalPrice: 55100,
      discount: 15,
      rating: 4.9,
      reviewCount: 61,
      badge: null,
      tags: ["5W-30", "API SN,ACEA C3", "가솔린/LPG/디젤"],
      category: "engineoil",
      image: "https://www.castrol.com/content/dam/castrol/products/magnatec-sn-c3-5w-30.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=캐스트롤+SN/C3+5W-30"
    },
    {
      id: "oil-003",
      brand: "캐스트롤",
      brandKey: "castrol",
      name: "마그네틱 SN/C3 5W-30",
      viscosity: "5W-30",
      grade: "API SN,ACEA C3",
      fuelType: "가솔린/LPG/디젤",
      price: 11834,
      originalPrice: 14200,
      discount: 17,
      rating: 5.0,
      reviewCount: 3,
      badge: null,
      tags: ["5W-30", "API SN,ACEA C3", "가솔린/LPG/디젤"],
      category: "engineoil",
      image: "https://www.castrol.com/content/dam/castrol/products/magnatec-sn-c3-5w-30.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=캐스트롤+SN/C3+5W-30"
    },
    {
      id: "oil-004",
      brand: "캐스트롤",
      brandKey: "castrol",
      name: "마그네틱 HYBRID 0W-20",
      viscosity: "0W-20",
      grade: "API SP",
      fuelType: "가솔린/LPG",
      price: 11931,
      originalPrice: 13700,
      discount: 13,
      rating: 4.9,
      reviewCount: 88,
      badge: null,
      tags: ["0W-20", "API SP", "가솔린/LPG"],
      category: "engineoil",
      image: "https://www.castrol.com/content/dam/castrol/products/magnatec-hybrid-0w-20.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=캐스트롤+HYBRID+0W-20"
    },
    {
      id: "oil-005",
      brand: "캐스트롤",
      brandKey: "castrol",
      name: "마그네틱 SN/C3 5W-40",
      viscosity: "5W-40",
      grade: "API SN,ACEA C3",
      fuelType: "가솔린/LPG/디젤",
      price: 9215,
      originalPrice: 10840,
      discount: 15,
      rating: 4.8,
      reviewCount: 42,
      badge: null,
      tags: ["5W-40", "API SN,ACEA C3", "가솔린/LPG/디젤"],
      category: "engineoil",
      image: "https://www.castrol.com/content/dam/castrol/products/magnatec-sn-c3-5w-40.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=캐스트롤+SN/C3+5W-40"
    },
    {
      id: "oil-006",
      brand: "캐스트롤",
      brandKey: "castrol",
      name: "마그네틱 STOP-START C2 5W-30",
      viscosity: "5W-30",
      grade: "ACEA C2",
      fuelType: "디젤",
      price: 14938,
      originalPrice: 18000,
      discount: 17,
      rating: 4.7,
      reviewCount: 28,
      badge: null,
      tags: ["5W-30", "ACEA C2", "디젤"],
      category: "engineoil",
      image: "https://www.castrol.com/content/dam/castrol/products/magnatec-stop-start-c2.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=캐스트롤+STOP-START+C2"
    },
    {
      id: "oil-007",
      brand: "캐스트롤",
      brandKey: "castrol",
      name: "GTX SP 0W-20 + 순정 필터 세트",
      viscosity: "0W-20",
      grade: "API SP,ACEA C5",
      fuelType: "가솔린/LPG/디젤",
      price: 39867,
      originalPrice: 42400,
      discount: 6,
      rating: 4.8,
      reviewCount: 69,
      badge: null,
      tags: ["0W-20", "API SP,ACEA C5", "가솔린/LPG/디젤"],
      category: "engineoil",
      image: "https://www.castrol.com/content/dam/castrol/products/gtx-sp-0w-20.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/117a65/ffffff?text=캐스트롤+GTX+SP+0W-20"
    },
    {
      id: "oil-008",
      brand: "캐스트롤",
      brandKey: "castrol",
      name: "GTX SP/C3 5W-30",
      viscosity: "5W-30",
      grade: "API SP,ACEA C3",
      fuelType: "가솔린/LPG/디젤",
      price: 57230,
      originalPrice: 62200,
      discount: 8,
      rating: 5.0,
      reviewCount: 2,
      badge: null,
      tags: ["5W-30", "API SP,ACEA C3", "가솔린/LPG/디젤"],
      category: "engineoil",
      image: "https://www.castrol.com/content/dam/castrol/products/gtx-sp-5w-30.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/117a65/ffffff?text=캐스트롤+GTX+SP/C3+5W-30"
    },
    {
      id: "oil-009",
      brand: "캐스트롤",
      brandKey: "castrol",
      name: "GTX SP 0W-20",
      viscosity: "0W-20",
      grade: "API SP,ACEA C5",
      fuelType: "가솔린/LPG/디젤",
      price: 10961,
      originalPrice: 11900,
      discount: 8,
      rating: 4.8,
      reviewCount: 69,
      badge: null,
      tags: ["0W-20", "API SP,ACEA C5", "가솔린/LPG/디젤"],
      category: "engineoil",
      image: "https://www.castrol.com/content/dam/castrol/products/gtx-sp-0w-20.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/117a65/ffffff?text=캐스트롤+GTX+SP+0W-20"
    },

    // ── 현대모비스 ────────────────────────────
    {
      id: "oil-010",
      brand: "현대모비스",
      brandKey: "hyundaimobis",
      name: "프리미엄 DPF 디젤 5W-30 + 순정 필터 세트",
      viscosity: "5W-30",
      grade: "ACEA C3",
      fuelType: "디젤",
      price: 61110,
      originalPrice: 66400,
      discount: 8,
      rating: 4.8,
      reviewCount: 106,
      badge: null,
      tags: ["5W-30", "ACEA C3", "디젤"],
      category: "engineoil",
      image: "https://www.hyundai-mobis.com/product/images/premium-dpf-5w30.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/2c3e50/ffffff?text=현대모비스+DPF+5W-30"
    },
    {
      id: "oil-011",
      brand: "현대모비스",
      brandKey: "hyundaimobis",
      name: "프리미엄 LF 5W-20 + 순정 필터 세트",
      viscosity: "5W-20",
      grade: "API SM",
      fuelType: "가솔린/LPG",
      price: 56163,
      originalPrice: 61000,
      discount: 8,
      rating: 4.9,
      reviewCount: 109,
      badge: null,
      tags: ["5W-20", "API SM", "가솔린/LPG"],
      category: "engineoil",
      image: "https://www.hyundai-mobis.com/product/images/premium-lf-5w20.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/2c3e50/ffffff?text=현대모비스+LF+5W-20"
    },
    {
      id: "oil-012",
      brand: "현대모비스",
      brandKey: "hyundaimobis",
      name: "터보씬 5W-30 + 순정 필터 세트",
      viscosity: "5W-30",
      grade: "ACEA A5",
      fuelType: "가솔린/LPG",
      price: 45590,
      originalPrice: 49500,
      discount: 8,
      rating: 4.7,
      reviewCount: 41,
      badge: null,
      tags: ["5W-30", "ACEA A5", "가솔린/LPG"],
      category: "engineoil",
      image: "https://www.hyundai-mobis.com/product/images/turbosyn-5w30.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/2c3e50/ffffff?text=현대모비스+터보씬+5W-30"
    },
  ],

  // =============================================
  // 🟢 와이퍼 (Wiper)
  // =============================================
  wiper: [
    {
      id: "wiper-001",
      brand: "BOSCH",
      brandKey: "bosch",
      name: "보쉬 클리어핏 V4 CLEARFIT 와이퍼",
      type: "일반형",
      coverage: "운전석+조수석",
      price: 8342,
      originalPrice: 8970,
      discount: 7,
      rating: 4.9,
      reviewCount: 148,
      badge: null,
      tags: ["일반형", "운전석+조수석"],
      category: "wiper",
      image: "https://media.bosch.com/product-images/ae-en/p-clearfit-v4.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=BOSCH+클리어핏+V4"
    },
    {
      id: "wiper-002",
      brand: "BOSCH",
      brandKey: "bosch",
      name: "보쉬 에어로트윈 AEROTWIN 2S2 와이퍼",
      type: "플랫형",
      coverage: "운전석+조수석",
      price: 29294,
      originalPrice: 30800,
      discount: 5,
      rating: 4.8,
      reviewCount: 75,
      badge: null,
      tags: ["플랫형", "운전석+조수석"],
      category: "wiper",
      image: "https://media.bosch.com/product-images/ae-en/p-aerotwin-2s2.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/1a5276/ffffff?text=BOSCH+에어로트윈"
    },
    {
      id: "wiper-003",
      brand: "미쉐린",
      brandKey: "michelin",
      name: "미쉐린 라디우스 RADIUS 하이브리드 와이퍼",
      type: "하이브리드",
      coverage: "운전석+조수석",
      price: 28518,
      originalPrice: 31700,
      discount: 10,
      rating: 4.9,
      reviewCount: 52,
      badge: null,
      tags: ["하이브리드", "운전석+조수석"],
      category: "wiper",
      image: "https://www.michelin.co.kr/sites/kr/files/styles/product/public/michelin-wiper-radius.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/c0392b/ffffff?text=미쉐린+라디우스+하이브리드"
    },
  ],

  // =============================================
  // 🔴 배터리 (Battery)
  // =============================================
  battery: [
    {
      id: "bat-001",
      brand: "델코",
      brandKey: "delkor",
      name: "델코 칼슘 배터리",
      model: "DF80L",
      price: 98000,
      originalPrice: 130000,
      discount: 25,
      rating: 4.6,
      reviewCount: 1131,
      badge: "BEST",
      tags: ["델코", "delkor", "칼슘"],
      category: "battery",
      image: "https://www.delkor.co.kr/images/products/calcium-df80l.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=델코+DF80L"
    },
    {
      id: "bat-002",
      brand: "델코",
      brandKey: "delkor",
      name: "델코 칼슘 배터리",
      model: "DIN74L (57412)",
      price: 95000,
      originalPrice: 125000,
      discount: 24,
      rating: 4.6,
      reviewCount: 1131,
      badge: null,
      tags: ["델코", "delkor", "칼슘", "DIN"],
      category: "battery",
      image: "https://www.delkor.co.kr/images/products/calcium-din74l.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=델코+DIN74L"
    },
    {
      id: "bat-003",
      brand: "델코",
      brandKey: "delkor",
      name: "델코 칼슘 배터리",
      model: "DIN60L",
      price: 82000,
      originalPrice: 108000,
      discount: 24,
      rating: 4.6,
      reviewCount: 1131,
      badge: null,
      tags: ["델코", "delkor", "칼슘", "DIN"],
      category: "battery",
      image: "https://www.delkor.co.kr/images/products/calcium-din60l.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=델코+DIN60L"
    },
    {
      id: "bat-004",
      brand: "델코",
      brandKey: "delkor",
      name: "델코 칼슘 배터리",
      model: "DF90R",
      price: 105000,
      originalPrice: 138000,
      discount: 24,
      rating: 4.6,
      reviewCount: 1131,
      badge: null,
      tags: ["델코", "delkor", "칼슘"],
      category: "battery",
      image: "https://www.delkor.co.kr/images/products/calcium-df90r.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=델코+DF90R"
    },
    {
      id: "bat-005",
      brand: "델코",
      brandKey: "delkor",
      name: "델코 칼슘 배터리",
      model: "DF90L",
      price: 105000,
      originalPrice: 138000,
      discount: 24,
      rating: 4.6,
      reviewCount: 1131,
      badge: null,
      tags: ["델코", "delkor", "칼슘"],
      category: "battery",
      image: "https://www.delkor.co.kr/images/products/calcium-df90l.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=델코+DF90L"
    },
    {
      id: "bat-006",
      brand: "델코",
      brandKey: "delkor",
      name: "델코 칼슘 배터리",
      model: "DF60L",
      price: 79000,
      originalPrice: 104000,
      discount: 24,
      rating: 4.6,
      reviewCount: 1131,
      badge: null,
      tags: ["델코", "delkor", "칼슘"],
      category: "battery",
      image: "https://www.delkor.co.kr/images/products/calcium-df60l.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=델코+DF60L"
    },
    {
      id: "bat-007",
      brand: "델코",
      brandKey: "delkor",
      name: "델코 칼슘 배터리",
      model: "DIN50L",
      price: 72000,
      originalPrice: 95000,
      discount: 24,
      rating: 4.6,
      reviewCount: 1131,
      badge: null,
      tags: ["델코", "delkor", "칼슘", "DIN"],
      category: "battery",
      image: "https://www.delkor.co.kr/images/products/calcium-din50l.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=델코+DIN50L"
    },
    {
      id: "bat-008",
      brand: "델코",
      brandKey: "delkor",
      name: "델코 칼슘 배터리",
      model: "DIN90L (59043)",
      price: 112000,
      originalPrice: 147000,
      discount: 24,
      rating: 4.6,
      reviewCount: 1131,
      badge: null,
      tags: ["델코", "delkor", "칼슘", "DIN"],
      category: "battery",
      image: "https://www.delkor.co.kr/images/products/calcium-din90l.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=델코+DIN90L"
    },
    {
      id: "bat-009",
      brand: "델코",
      brandKey: "delkor",
      name: "델코 칼슘 배터리",
      model: "DF40AL",
      price: 65000,
      originalPrice: 86000,
      discount: 24,
      rating: 4.6,
      reviewCount: 1131,
      badge: null,
      tags: ["델코", "delkor", "칼슘"],
      category: "battery",
      image: "https://www.delkor.co.kr/images/products/calcium-df40al.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=델코+DF40AL"
    },
    {
      id: "bat-010",
      brand: "델코",
      brandKey: "delkor",
      name: "델코 칼슘 배터리",
      model: "DF100L",
      price: 128000,
      originalPrice: 168000,
      discount: 24,
      rating: 4.6,
      reviewCount: 1131,
      badge: null,
      tags: ["델코", "delkor", "칼슘"],
      category: "battery",
      image: "https://www.delkor.co.kr/images/products/calcium-df100l.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=델코+DF100L"
    },
    {
      id: "bat-011",
      brand: "델코",
      brandKey: "delkor",
      name: "델코 칼슘 배터리",
      model: "DIN60HL (55530)",
      price: 88000,
      originalPrice: 116000,
      discount: 24,
      rating: 4.6,
      reviewCount: 1131,
      badge: null,
      tags: ["델코", "delkor", "칼슘", "DIN"],
      category: "battery",
      image: "https://www.delkor.co.kr/images/products/calcium-din60hl.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=델코+DIN60HL"
    },
    {
      id: "bat-012",
      brand: "델코",
      brandKey: "delkor",
      name: "델코 칼슘 배터리",
      model: "DIN74R",
      price: 95000,
      originalPrice: 125000,
      discount: 24,
      rating: 4.6,
      reviewCount: 1131,
      badge: null,
      tags: ["델코", "delkor", "칼슘", "DIN"],
      category: "battery",
      image: "https://www.delkor.co.kr/images/products/calcium-din74r.jpg",
      fallbackImage: "https://via.placeholder.com/300x200/922b21/ffffff?text=델코+DIN74R"
    },
  ]
};

// =============================================
// 브랜드 목록 정의 (필터용)
// =============================================
const TT_BRANDS = {
  tire: [
    { key: "kumho",       label: "금호",       logo: "🔵", keywords: ["금호", "kumho"] },
    { key: "hankook",     label: "한국타이어", logo: "🔴", keywords: ["한국타이어", "hankook"] },
    { key: "nexen",       label: "넥센",       logo: "🟢", keywords: ["넥센", "nexen"] },
    { key: "michelin",    label: "미쉐린",     logo: "⚪", keywords: ["미쉐린", "michelin"] },
    { key: "bridgestone", label: "브리지스톤", logo: "🟡", keywords: ["브리지스톤", "bridgestone"] },
    { key: "continental", label: "콘티넨탈",   logo: "⚫", keywords: ["콘티넨탈", "continental"] },
    { key: "goodyear",    label: "굿이어",     logo: "🟠", keywords: ["굿이어", "goodyear", "쿠퍼"] },
    { key: "pirelli",     label: "피렐리",     logo: "🟤", keywords: ["피렐리", "pirelli"] },
  ],
  engineoil: [
    { key: "castrol",       label: "캐스트롤",   logo: "🔵", keywords: ["캐스트롤", "castrol"] },
    { key: "hyundaimobis",  label: "현대모비스", logo: "🔴", keywords: ["현대모비스", "hyundaimobis"] },
    { key: "mobil",         label: "모빌",       logo: "🔴", keywords: ["모빌", "mobil"] },
    { key: "shell",         label: "쉘",         logo: "🟡", keywords: ["쉘", "shell"] },
  ],
  wiper: [
    { key: "bosch",    label: "BOSCH",  logo: "🔵", keywords: ["BOSCH", "보쉬", "bosch"] },
    { key: "michelin", label: "미쉐린", logo: "⚪", keywords: ["미쉐린", "michelin"] },
  ],
  battery: [
    { key: "delkor",  label: "델코",   logo: "🔴", keywords: ["델코", "delkor"] },
    { key: "atlasbx", label: "아틀라스", logo: "🔵", keywords: ["아틀라스", "atlasbx"] },
    { key: "rocket",  label: "로케트", logo: "🟡", keywords: ["로케트", "rocket"] },
  ]
};

// =============================================
// 유틸리티 함수
// =============================================

/**
 * 카테고리별 전체 상품 반환
 */
function getProductsByCategory(category) {
  return TT_PRODUCTS[category] || [];
}

/**
 * 브랜드 키로 필터링
 */
function getProductsByBrand(category, brandKey) {
  return getProductsByCategory(category).filter(p => p.brandKey === brandKey);
}

/**
 * 검색어로 상품 검색 (이름, 브랜드, 태그)
 */
function searchProducts(query, category = null) {
  const q = query.toLowerCase();
  const pool = category ? getProductsByCategory(category)
    : Object.values(TT_PRODUCTS).flat();

  return pool.filter(p =>
    p.name.toLowerCase().includes(q) ||
    p.brand.toLowerCase().includes(q) ||
    (p.brandKey && p.brandKey.includes(q)) ||
    (p.tags && p.tags.some(t => t.toLowerCase().includes(q))) ||
    (p.size && p.size.includes(q)) ||
    (p.model && p.model.toLowerCase().includes(q))
  );
}

/**
 * 이미지 로드 실패 시 fallback 처리
 */
function getProductImage(product) {
  return product.image || product.fallbackImage ||
    `https://via.placeholder.com/300x200/cccccc/333333?text=${encodeURIComponent(product.name)}`;
}

/**
 * 가격 포맷 (원 단위)
 */
function formatPrice(price) {
  return price.toLocaleString('ko-KR') + '원';
}

/**
 * 별점 렌더링용 문자열
 */
function renderStars(rating) {
  const full = Math.floor(rating);
  const half = rating % 1 >= 0.5;
  return '★'.repeat(full) + (half ? '½' : '') + '☆'.repeat(5 - full - (half ? 1 : 0));
}

// 전역 노출
if (typeof window !== 'undefined') {
  window.TT_PRODUCTS = TT_PRODUCTS;
  window.TT_BRANDS   = TT_BRANDS;
  window.getProductsByCategory = getProductsByCategory;
  window.getProductsByBrand    = getProductsByBrand;
  window.searchProducts        = searchProducts;
  window.getProductImage       = getProductImage;
  window.formatPrice           = formatPrice;
  window.renderStars           = renderStars;
}

// Node.js/CommonJS 환경
if (typeof module !== 'undefined') {
  module.exports = {
    TT_PRODUCTS, TT_BRANDS,
    getProductsByCategory, getProductsByBrand,
    searchProducts, getProductImage,
    formatPrice, renderStars
  };
}
