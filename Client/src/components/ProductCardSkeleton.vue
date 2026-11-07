<template>
  <div class="bg-white dark:bg-[#1E120B] rounded-2xl border border-gray-200/80 dark:border-[#382318] p-3 sm:p-3.5 space-y-3 flex flex-col justify-between h-full shadow-2xs overflow-hidden select-none">
    
    <!-- Image Box Skeleton with Shimmer Wave -->
    <div class="relative aspect-4/3 rounded-xl overflow-hidden shimmer-wave bg-[#F0E8DF] dark:bg-[#25150E]">
      <!-- Floating Tag Placeholder -->
      <div class="absolute top-2.5 left-2.5 w-16 h-5 rounded-full bg-white/70 dark:bg-white/10"></div>
      <!-- Wishlist Placeholder -->
      <div class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full bg-white/70 dark:bg-white/10"></div>
    </div>

    <!-- Details Skeleton -->
    <div class="space-y-2 flex-1 flex flex-col justify-between pt-1">
      <div class="space-y-2">
        <!-- Title Line -->
        <div class="h-4 rounded-md shimmer-wave bg-[#F0E8DF] dark:bg-[#25150E] w-3/4"></div>
        
        <!-- Stars & Reviews Line -->
        <div class="flex items-center space-x-2 pt-0.5">
          <div class="h-3 rounded-md shimmer-wave bg-[#F0E8DF] dark:bg-[#25150E] w-16"></div>
          <div class="h-3 rounded-md shimmer-wave bg-[#F0E8DF] dark:bg-[#25150E] w-10"></div>
        </div>

        <!-- Short Description Line -->
        <div class="h-3 rounded-md shimmer-wave bg-[#F0E8DF] dark:bg-[#25150E] w-full"></div>
      </div>

      <!-- Price & Action Button Row -->
      <div class="pt-2.5 border-t border-gray-100 dark:border-[#331E14] flex items-center justify-between gap-2">
        <div class="space-y-1">
          <div class="h-2.5 rounded shimmer-wave bg-[#F0E8DF] dark:bg-[#25150E] w-8"></div>
          <div class="h-4 rounded shimmer-wave bg-[#F0E8DF] dark:bg-[#25150E] w-16"></div>
        </div>
        <div class="w-16 sm:w-20 h-9 rounded-xl shimmer-wave bg-[#F0E8DF] dark:bg-[#25150E]"></div>
      </div>
    </div>

  </div>
</template>

<script setup>
// Reusable SweetBakes skeleton placeholder component
</script>
