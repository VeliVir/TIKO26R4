<?php
function getTarvikeSumSQL(): string {
    return "(
        SELECT 
            st.sopimus_id, 
            COALESCE(
                SUM(
                    COALESCE(st.maara, 0) 
                    * COALESCE(t.hankintahinta, 0) 
                    * COALESCE(st.hintatekija, 1)
                ), 
            0) AS t_summa
        FROM Sopimus_tarvike st
        JOIN Tarvike t ON t.tarvike_id = st.tarvike_id
        GROUP BY st.sopimus_id
    )";
}

function getSuoritusSumSQL(): string {
    return "(
        SELECT 
            ss.sopimus_id,
            COALESCE(
                SUM(
                    (
                        CASE 
                            WHEN ss.urakka_hinta IS NOT NULL 
                                THEN ss.urakka_hinta
                            ELSE 
                                COALESCE(ss.tyomaara_tunneilla, 0) 
                                * COALESCE(s.hinta, 0)
                        END
                    ) * COALESCE(ss.hintatekija, 1)
                ),
            0) AS s_summa
        FROM Sopimus_suoritus ss
        JOIN Suoritus s ON s.suoritus_id = ss.suoritus_id
        GROUP BY ss.sopimus_id
    )";
}
?>