<?php
function getTarvikeSumSQL(): string {
    return "(
        SELECT st.sopimus_id, SUM(st.maara * t.hankintahinta * st.hintatekija) AS t_summa
        FROM Sopimus_tarvike st
        JOIN Tarvike t ON t.tarvike_id = st.tarvike_id
        GROUP BY st.sopimus_id
    )";
}

function getSuoritusSumSQL(): string {
    return "(
        SELECT 
            ss.sopimus_id,
            SUM(
                (
                    CASE 
                        WHEN ss.urakka_hinta IS NOT NULL 
                            THEN ss.urakka_hinta
                        ELSE ss.tyomaara_tunneilla * s.hinta
                    END
                ) * ss.hintatekija
            ) AS s_summa
        FROM Sopimus_suoritus ss
        JOIN Suoritus s ON s.suoritus_id = ss.suoritus_id
        GROUP BY ss.sopimus_id
    )";
}
?>