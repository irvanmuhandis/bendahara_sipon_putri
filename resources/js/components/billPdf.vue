<!-- PdfGenerator.vue -->

<template>
    <div class="d-flex justify-content-center">
        <div id="pdf-content" style="height:1000px;width:700px ;" class="overflow-auto card m-2 shadow p-5 text-times">
            <!-- header -->
            <div class="row m-2 ml-3">
                <div class="col">
                    <div class="">
                        <img src="../../../public/images/kop_surat.png" style="width: 400px; object-fit: fill;"
                            class="img-fluid ">
                    </div>
                </div>
            </div>
            <div class="row m-2">
                <div class="col">
                    <div class="">
                        <table class="table-sm table-borderless p-0">
                            <tbody>
                                <tr class="p-0">
                                    <td class="p-0" scope="row">No</td>
                                    <td class="p-0 pl-1">:</td>
                                    <td class="p-0 pl-1">noSurat</td>
                                </tr>
                                <tr class="p-0">
                                    <td class="p-0" scope="row">Hal</td>
                                    <td class="p-0 pl-1">:</td>
                                    <td class="p-0 pl-1 text-bold">Pemberitahuan Tunggakan</td>
                                </tr>
                                <tr class="p-0">
                                    <td class="p-0" scope="row">Lamp</td>
                                    <td class="p-0 pl-1">:</td>
                                    <td class="p-0 pl-1">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col text-right">
                    <span>Semarang, 6 Desember 2023</span>
                </div>
            </div>
            <!-- Salam -->
            <div class="row m-2">
                <div class="col">
                    Yth. <strong>Wali Santri ...</strong><br>di Tempat
                    <br><br>
                    <strong><i>Assalamu'alaikum Warahmatullahi Wabarakatuh</i></strong>
                    <br><br>
                </div>
            </div>
            <!-- isi -->
            <div class="row m-2">
                <div class="col text-justify">
                    <span>
                        Bersama surat ini kami memberitahukan bahwa menurut catatan administrasi, santri yang bersangkutan
                        memiliki tunggakan administrasi syahriyah pondok sebagai berikut:
                    </span>
                    <br><br>
                    <span>
                        Nama : Andi
                        <br>Jumlah tunggakan : Rp.1000
                    </span>
                    <br><br>
                    <span>
                        Rekapan tunggakan terhitung dari Bulan Januari 2023 sampai Bulan Desember 2023. Kami mohon sekiranya
                        Bapak/ Ibu dapat segera melunasi tunggakan tersebut. Informasi lebih lanjut dapat menghubungi 085
                        701
                        514 915 a.n. Muhammad Irvan Muhandis.
                    </span>
                    <br>
                    <span>
                        Demikian pemberitahuan ini kami sampaikan, atas perhatian Bapak/ Ibu kami ucapkan terima kasih.
                    </span>
                    <br><br>
                    <strong><i>Wassalamu’alaikum Warahmatullahi Wabarokatuh
                        </i></strong>
                    <br>
                    <br>
                    <div class="text-center w-100">Mengetahui,</div>
                </div>
            </div>

            <!-- TTD -->
            <div class="row m-2">
                <div class="col text-center">
                    <div class="pb-4 mb-5">Lurah Pondok Putra</div>
                    <div class=""><u><strong>Isman Nasik, M.Si., AH</strong></u></div>
                </div>
                <div class="col text-center">
                    <div class="pb-4 mb-5">Bendahara Pondok Putra</div>
                    <div class=""><u><strong>Muhammad Irvan Muhandis, S.Si</strong></u></div>
                </div>
            </div>
        </div>
    </div>

</template>

